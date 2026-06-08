<?php

namespace App\Http\Controllers\Subscriber;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\Subscription;
use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    // ─── Prorata Calculation Helper ────────────────────────────────────────────

    /**
     * Calculate prorata upgrade credit and amount due.
     *
     * @param  Subscription   $currentSubscription  Active current subscription
     * @param  SubscriptionPlan $newPlan            Target upgrade plan
     * @return array{
     *   is_upgrade: bool,
     *   remaining_days: int,
     *   current_plan_price: float,
     *   current_plan_duration: int,
     *   daily_rate: float,
     *   unused_credit: float,
     *   new_plan_price: float,
     *   prorata_amount: float,
     * }
     */
    protected function calculateUpgradeProrata(Subscription $currentSubscription, SubscriptionPlan $newPlan): array
    {
        $currentPlan = $currentSubscription->plan;

        // Days remaining in current subscription (0 if already expired)
        $endDate = $currentSubscription->ends_at ?? now();
        $remainingDays = max(0, (int) now()->diffInDays($endDate, false));

        $currentPlanPrice    = (float) ($currentPlan->price ?? 0);
        $currentPlanDuration = max(1, (int) ($currentPlan->duration_days ?? 30));
        $newPlanPrice        = (float) $newPlan->price;

        // Daily cost of the current plan
        $dailyRate   = $currentPlanPrice / $currentPlanDuration;

        // Credit for unused days on current plan
        $unusedCredit = round($remainingDays * $dailyRate, 2);

        // Amount to charge: difference (minimum ₹0)
        $prorataAmount = max(0, round($newPlanPrice - $unusedCredit, 2));

        // It's an upgrade only if new plan is more expensive
        $isUpgrade = $newPlanPrice > $currentPlanPrice;

        return [
            'is_upgrade'           => $isUpgrade,
            'remaining_days'       => $remainingDays,
            'current_plan_price'   => $currentPlanPrice,
            'current_plan_duration'=> $currentPlanDuration,
            'daily_rate'           => $dailyRate,
            'unused_credit'        => $unusedCredit,
            'new_plan_price'       => $newPlanPrice,
            'prorata_amount'       => $prorataAmount,
        ];
    }

    // ─── Controller Actions ────────────────────────────────────────────────────

    public function index()
    {
        $user = auth()->user();
        $activeSubscription = $user->activeSubscription();
        $invoices = Invoice::where('user_id', $user->id)->with('subscription.plan')->latest()->get();
        $payments = Payment::where('user_id', $user->id)->latest()->take(10)->get();

        return view('subscriber-panel.subscription.index', compact('activeSubscription', 'invoices', 'payments'));
    }

    public function plans()
    {
        $plans = SubscriptionPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
        $currentSubscription = auth()->user()->activeSubscription();
        return view('subscriber-panel.subscription.plans', compact('plans', 'currentSubscription'));
    }

    public function checkout(SubscriptionPlan $plan)
    {
        if (!$plan->is_active) abort(404);
        $user = auth()->user();
        $currentSubscription = $user->activeSubscription();

        // Calculate prorata if user already has an active subscription
        $prorata = null;
        $isUpgrade = false;
        if ($currentSubscription && $currentSubscription->plan_id !== $plan->id && !$plan->is_trial) {
            $prorata = $this->calculateUpgradeProrata($currentSubscription, $plan);
            $isUpgrade = $prorata['is_upgrade'];
        }

        return view('subscriber-panel.subscription.checkout', compact('plan', 'currentSubscription', 'prorata', 'isUpgrade'));
    }

    public function processDummyPayment(Request $request, SubscriptionPlan $plan)
    {
        $gateway = $request->input('gateway', 'dummy');

        if ($gateway === 'stripe') {
            $request->validate([
                'card_name'   => 'required|string',
                'card_number' => 'required|string',
            ]);
        } elseif ($gateway === 'paypal') {
            $request->validate([
                'paypal_email' => 'required|email',
            ]);
        }

        $user = auth()->user();
        $isTrial = $plan->is_trial;
        $profile = $user->subscriberProfile;
        $currentSubscription = $user->activeSubscription();

        // ── Prorata calculation (for upgrades/switches with existing active sub) ──
        $prorataData = null;
        if ($currentSubscription && !$isTrial) {
            $prorataData = $this->calculateUpgradeProrata($currentSubscription, $plan);
        }

        // Determine amounts
        $subtotal = $isTrial ? 0 : ($prorataData ? $prorataData['prorata_amount'] : $plan->price);
        $tax = 0;
        if (!$isTrial) {
            $tax = round($subtotal * 0.18, 2);
        }
        $total = $subtotal + $tax;

        // Build line items
        $lineItems = [];
        if ($isTrial) {
            $lineItems[] = ['description' => $plan->name . ' Plan (Free Trial — ' . $plan->trial_days . ' days)', 'amount' => 0];
        } elseif ($prorataData) {
            $lineItems[] = ['description' => $plan->name . ' Plan (' . $plan->duration_days . ' days) — Full Price', 'amount' => $prorataData['new_plan_price']];
            $lineItems[] = ['description' => 'Prorata credit (' . $prorataData['remaining_days'] . ' unused days on previous plan)', 'amount' => -$prorataData['unused_credit']];
            $lineItems[] = ['description' => 'Net Upgrade Amount', 'amount' => $prorataData['prorata_amount']];
        } else {
            $lineItems[] = ['description' => $plan->name . ' Plan (' . $plan->duration_days . ' days)', 'amount' => $plan->price];
        }

        // Create payment record
        $payment = Payment::create([
            'user_id'              => $user->id,
            'subscription_plan_id' => $plan->id,
            'transaction_id'       => $isTrial ? 'TRIAL-' . strtoupper(Str::random(12)) : 'TXN-' . strtoupper(Str::random(12)),
            'gateway'              => $isTrial ? 'free_trial' : $gateway,
            'gateway_payment_id'   => $isTrial ? 'trial_' . Str::random(14) : $request->input('gateway_payment_id', 'pay_' . Str::random(14)),
            'gateway_order_id'     => $isTrial ? 'trial_order_' . Str::random(14) : $request->input('gateway_order_id', 'order_' . Str::random(14)),
            'amount'               => $total,
            'currency'             => $plan->currency,
            'status'               => 'success',
            'paid_at'              => Carbon::now(),
            'gateway_response'     => [
                'message'        => $isTrial ? 'Free trial subscription activated' : 'Simulated ' . ucfirst($gateway) . ' sandbox payment success',
                'mode'           => $isTrial ? 'trial' : 'test',
                'email'          => $user->email,
                'prorata_credit' => $prorataData ? $prorataData['unused_credit'] : null,
            ],
            'notes' => $isTrial
                ? 'Free Trial - No Payment Required'
                : ($prorataData
                    ? 'Prorata Upgrade — Credit: ₹' . number_format($prorataData['unused_credit'], 2) . ' | Net: ₹' . number_format($prorataData['prorata_amount'], 2)
                    : 'Demo payment - Test mode (' . ucfirst($gateway) . ')'),
        ]);

        // Cancel old subscriptions
        Subscription::where('user_id', $user->id)
            ->whereIn('status', ['active', 'trial'])
            ->update(['status' => 'cancelled', 'cancelled_at' => now()]);

        // Create new subscription — always grant full duration
        $subscription = Subscription::create([
            'user_id'              => $user->id,
            'subscription_plan_id' => $plan->id,
            'status'               => $isTrial ? 'trial' : 'active',
            'starts_at'            => Carbon::now(),
            'ends_at'              => Carbon::now()->addDays($isTrial ? $plan->trial_days : $plan->duration_days),
            'trial_ends_at'        => $isTrial ? Carbon::now()->addDays($plan->trial_days) : null,
        ]);

        // Create invoice
        Invoice::create([
            'user_id'         => $user->id,
            'subscription_id' => $subscription->id,
            'payment_id'      => $payment->id,
            'invoice_number'  => Invoice::generateNumber(),
            'subtotal'        => $subtotal,
            'tax'             => $tax,
            'total'           => $total,
            'currency'        => $plan->currency,
            'status'          => 'paid',
            'paid_date'       => Carbon::now(),
            'line_items'      => $lineItems,
        ]);

        // Notify subscriber
        try {
            $notifMessage = $prorataData
                ? 'Your subscription upgrade payment of ' . $plan->currency . ' ' . number_format($total, 2) . ' was successful. Prorata credit of ₹' . number_format($prorataData['unused_credit'], 2) . ' was applied.'
                : 'Your subscription payment of ' . $plan->currency . ' ' . number_format($plan->price, 2) . ' was successful.';
            $user->notify(new \App\Notifications\PaymentSuccessNotification([
                'title'   => $prorataData ? 'Subscription Upgraded!' : 'Payment Successful',
                'message' => $notifMessage,
            ]));
        } catch (\Exception $e) {}

        $profile = $user->subscriberProfile;

        // Notify admin
        try {
            $admins = \App\Models\User::role(['Super Admin', 'Admin'])->get();
            if ($admins->isNotEmpty()) {
                \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\PaymentSuccessNotification([
                    'title'   => $prorataData ? 'New B2B Subscription Upgrade' : 'New B2B Subscription Payment',
                    'message' => $user->name . ' (' . ($profile->company_name ?? '') . ') has ' . ($prorataData ? 'upgraded to' : 'subscribed to') . ' ' . $plan->name . ' plan.',
                ]));
            }
        } catch (\Exception $e) {}

        if ($profile && $profile->status === 'pending') {
            $profile->update([
                'status'      => 'approved',
                'store_status'=> 'live',
                'is_verified' => true,
            ]);
        }

        $successMsg = $prorataData
            ? '🎉 Plan upgraded to ' . $plan->name . '! Prorata credit of ₹' . number_format($prorataData['unused_credit'], 2) . ' was applied. You saved ₹' . number_format($prorataData['unused_credit'], 2) . '!'
            : '🎉 Subscription activated! Welcome to the ' . $plan->name . ' plan.';

        return redirect()->route('subscriber.subscription.index')->with('success', $successMsg);
    }

    public function createRazorpayOrder(Request $request, SubscriptionPlan $plan)
    {
        if (!$plan->is_active) {
            return response()->json(['error' => 'Selected subscription plan is inactive.'], 400);
        }

        $key    = config('services.razorpay.key');
        $secret = config('services.razorpay.secret');

        if (empty($key) || empty($secret) || $key === 'rzp_test_change_me' || $secret === 'change_me_secret') {
            return response()->json(['error' => 'Razorpay credentials are not configured yet. Please contact the administrator.'], 400);
        }

        $user    = auth()->user();
        $profile = $user->subscriberProfile;
        $currentSubscription = $user->activeSubscription();

        // Prorata calculation
        $prorataData = null;
        if ($currentSubscription) {
            $prorataData = $this->calculateUpgradeProrata($currentSubscription, $plan);
        }

        $subtotal = $prorataData ? $prorataData['prorata_amount'] : $plan->price;
        $tax = round($subtotal * 0.18, 2);
        $total = $subtotal + $tax;

        // Amount in paise (multiply by 100)
        $amount = (int) round($total * 100);

        // Razorpay requires minimum ₹1 (100 paise). If prorata makes it ₹0, handle as free
        if ($amount <= 0) {
            return response()->json([
                'prorata_free' => true,
                'message'      => 'Your upgrade is covered by prorata credit. No payment required.',
                'prorata'      => $prorataData,
            ]);
        }

        try {
            $response = Http::withBasicAuth($key, $secret)
                ->post('https://api.razorpay.com/v1/orders', [
                    'amount'   => $amount,
                    'currency' => 'INR',
                    'receipt'  => 'rcpt_' . Str::random(10),
                    'notes'    => [
                        'type'          => $prorataData ? 'upgrade_prorata' : 'new_subscription',
                        'prorata_credit'=> $prorataData ? $prorataData['unused_credit'] : 0,
                    ],
                ]);

            if ($response->failed()) {
                Log::error('Razorpay Order Creation Failed: ' . $response->body());
                $errorData    = $response->json();
                $errorMessage = $errorData['error']['description'] ?? 'Unable to initiate transaction with Razorpay. Try again later.';
                return response()->json(['error' => $errorMessage], 400);
            }

            $order = $response->json();

            return response()->json([
                'id'       => $order['id'],
                'amount'   => $order['amount'],
                'currency' => $order['currency'],
                'key'      => $key,
                'prorata'  => $prorataData,
                'user'     => [
                    'name'  => auth()->user()->name,
                    'email' => auth()->user()->email,
                    'phone' => auth()->user()->subscriberProfile->phone ?? '',
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Razorpay exception: ' . $e->getMessage());
            return response()->json(['error' => 'Server error occurred during payment setup: ' . $e->getMessage()], 500);
        }
    }

    public function verifyRazorpayPayment(Request $request, SubscriptionPlan $plan)
    {
        $request->validate([
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id'   => 'required|string',
            'razorpay_signature'  => 'required|string',
        ]);

        $paymentId = $request->input('razorpay_payment_id');
        $orderId   = $request->input('razorpay_order_id');
        $signature = $request->input('razorpay_signature');

        $key    = config('services.razorpay.key');
        $secret = config('services.razorpay.secret');

        // Verify the signature
        $expectedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $secret);
        $isTest = str_starts_with($key, 'rzp_test');

        if ($expectedSignature !== $signature) {
            Log::warning('Razorpay payment signature mismatch.', [
                'expected'   => $expectedSignature,
                'received'   => $signature,
                'order_id'   => $orderId,
                'payment_id' => $paymentId,
            ]);

            Payment::create([
                'user_id'              => auth()->id(),
                'subscription_plan_id' => $plan->id,
                'transaction_id'       => $paymentId,
                'gateway'              => 'razorpay',
                'gateway_payment_id'   => $paymentId,
                'gateway_order_id'     => $orderId,
                'amount'               => $plan->price,
                'currency'             => 'INR',
                'status'               => 'failed',
                'gateway_response'     => ['error' => 'Signature mismatch during verification'],
                'notes'                => $isTest
                    ? 'Razorpay Sandbox Payment Failed: Signature mismatch verification'
                    : 'Razorpay Live Payment Failed: Signature mismatch verification',
            ]);

            return redirect()->route('subscriber.subscription.checkout', $plan->id)
                ->with('error', '❌ Secure verification failed! Signature mismatch. Your payment was not processed.');
        }

        // Signature matches — activate subscription!
        $user    = auth()->user();
        $profile = $user->subscriberProfile;
        $currentSubscription = $user->activeSubscription();

        // Prorata calculation
        $prorataData = null;
        if ($currentSubscription) {
            $prorataData = $this->calculateUpgradeProrata($currentSubscription, $plan);
        }

        $subtotal = $prorataData ? $prorataData['prorata_amount'] : $plan->price;
        $tax = round($subtotal * 0.18, 2);
        $total = $subtotal + $tax;

        // Build line items
        $lineItems = [];
        if ($prorataData) {
            $lineItems[] = ['description' => $plan->name . ' Plan (' . $plan->duration_days . ' days) — Full Price', 'amount' => $prorataData['new_plan_price']];
            $lineItems[] = ['description' => 'Prorata credit (' . $prorataData['remaining_days'] . ' unused days on previous plan)', 'amount' => -$prorataData['unused_credit']];
            $lineItems[] = ['description' => 'Net Upgrade Amount', 'amount' => $prorataData['prorata_amount']];
        } else {
            $lineItems[] = ['description' => $plan->name . ' Plan (' . $plan->duration_days . ' days)', 'amount' => $plan->price];
        }

        // Create successful payment record
        $payment = Payment::create([
            'user_id'              => $user->id,
            'subscription_plan_id' => $plan->id,
            'transaction_id'       => $paymentId,
            'gateway'              => 'razorpay',
            'gateway_payment_id'   => $paymentId,
            'gateway_order_id'     => $orderId,
            'amount'               => $total,
            'currency'             => 'INR',
            'status'               => 'success',
            'paid_at'              => Carbon::now(),
            'gateway_response'     => array_merge($request->all(), [
                'prorata_credit' => $prorataData ? $prorataData['unused_credit'] : null,
            ]),
            'notes' => $prorataData
                ? ($isTest ? 'Razorpay Sandbox — Prorata Upgrade' : 'Razorpay Live — Prorata Upgrade') . ' | Credit: ₹' . number_format($prorataData['unused_credit'], 2)
                : ($isTest ? 'Razorpay Sandbox Payment Verified Securely' : 'Razorpay Live Production Payment Verified Securely'),
        ]);

        // Cancel old subscriptions
        Subscription::where('user_id', $user->id)
            ->whereIn('status', ['active', 'trial'])
            ->update(['status' => 'cancelled', 'cancelled_at' => now()]);

        // Create new subscription — always grant full plan duration
        $subscription = Subscription::create([
            'user_id'              => $user->id,
            'subscription_plan_id' => $plan->id,
            'status'               => 'active',
            'starts_at'            => Carbon::now(),
            'ends_at'              => Carbon::now()->addDays($plan->duration_days),
        ]);

        // Create invoice
        Invoice::create([
            'user_id'         => $user->id,
            'subscription_id' => $subscription->id,
            'payment_id'      => $payment->id,
            'invoice_number'  => Invoice::generateNumber(),
            'subtotal'        => $subtotal,
            'tax'             => $tax,
            'total'           => $total,
            'currency'        => 'INR',
            'status'          => 'paid',
            'paid_date'       => Carbon::now(),
            'line_items'      => $lineItems,
        ]);

        // Notify subscriber
        try {
            $notifMessage = $prorataData
                ? 'Your plan upgrade payment of INR ' . number_format($total, 2) . ' was successful. Prorata credit of ₹' . number_format($prorataData['unused_credit'], 2) . ' was applied.'
                : 'Your subscription payment of INR ' . number_format($plan->price, 2) . ' was successful.';
            $user->notify(new \App\Notifications\PaymentSuccessNotification([
                'title'   => $prorataData ? 'Plan Upgraded Successfully!' : 'Payment Successful',
                'message' => $notifMessage,
            ]));
        } catch (\Exception $e) {
            Log::error('Notification failed: ' . $e->getMessage());
        }

        $profile = $user->subscriberProfile;

        // Notify admin
        try {
            $admins = \App\Models\User::role(['Super Admin', 'Admin'])->get();
            if ($admins->isNotEmpty()) {
                \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\PaymentSuccessNotification([
                    'title'   => $prorataData ? 'New B2B Subscription Upgrade (Razorpay)' : 'New B2B Subscription Payment (Razorpay)',
                    'message' => $user->name . ' (' . ($profile->company_name ?? '') . ') has ' . ($prorataData ? 'upgraded to' : 'subscribed to') . ' ' . $plan->name . ' plan.',
                ]));
            }
        } catch (\Exception $e) {
            Log::error('Admin notification failed: ' . $e->getMessage());
        }

        if ($profile && $profile->status === 'pending') {
            $profile->update([
                'status'      => 'approved',
                'store_status'=> 'live',
                'is_verified' => true,
            ]);
        }

        $successMsg = $prorataData
            ? '🎉 Plan upgraded to ' . $plan->name . '! Prorata credit of ₹' . number_format($prorataData['unused_credit'], 2) . ' was applied.'
            : '🎉 Subscription activated! Welcome to the ' . $plan->name . ' plan.';

        return redirect()->route('subscriber.subscription.index')->with('success', $successMsg);
    }

    public function invoiceDownload(Invoice $invoice)
    {
        if ($invoice->user_id !== auth()->id()) abort(403);
        $invoice->load('subscription.plan', 'payment');
        $user    = auth()->user();
        $profile = $user->subscriberProfile;
        return view('subscriber-panel.subscription.invoice-pdf', compact('invoice', 'user', 'profile'));
    }
}
