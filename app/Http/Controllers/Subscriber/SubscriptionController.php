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
        return view('subscriber-panel.subscription.checkout', compact('plan', 'currentSubscription'));
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

        // Create payment record (handling free trial bypass)
        $payment = Payment::create([
            'user_id'              => $user->id,
            'subscription_plan_id' => $plan->id,
            'transaction_id'       => $isTrial ? 'TRIAL-' . strtoupper(Str::random(12)) : 'TXN-' . strtoupper(Str::random(12)),
            'gateway'              => $isTrial ? 'free_trial' : $gateway,
            'gateway_payment_id'   => $isTrial ? 'trial_' . Str::random(14) : $request->input('gateway_payment_id', 'pay_' . Str::random(14)),
            'gateway_order_id'     => $isTrial ? 'trial_order_' . Str::random(14) : $request->input('gateway_order_id', 'order_' . Str::random(14)),
            'amount'               => $isTrial ? 0 : $plan->price,
            'currency'             => $plan->currency,
            'status'               => 'success',
            'paid_at'              => Carbon::now(),
            'gateway_response'     => [
                'message' => $isTrial ? 'Free trial subscription activated' : 'Simulated ' . ucfirst($gateway) . ' sandbox payment success',
                'mode' => $isTrial ? 'trial' : 'test',
                'email' => $user->email,
            ],
            'notes'                => $isTrial ? 'Free Trial - No Payment Required' : 'Demo payment - Test mode (' . ucfirst($gateway) . ')',
        ]);

        // Cancel old subscriptions
        Subscription::where('user_id', $user->id)
            ->whereIn('status', ['active', 'trial'])
            ->update(['status' => 'cancelled', 'cancelled_at' => now()]);

        // Create new subscription with trial status and trial days if applicable
        $subscription = Subscription::create([
            'user_id'              => $user->id,
            'subscription_plan_id' => $plan->id,
            'status'               => $isTrial ? 'trial' : 'active',
            'starts_at'            => Carbon::now(),
            'ends_at'              => Carbon::now()->addDays($isTrial ? $plan->trial_days : $plan->duration_days),
            'trial_ends_at'        => $isTrial ? Carbon::now()->addDays($plan->trial_days) : null,
        ]);

        // Create invoice
        $invoice = Invoice::create([
            'user_id'         => $user->id,
            'subscription_id' => $subscription->id,
            'payment_id'      => $payment->id,
            'invoice_number'  => Invoice::generateNumber(),
            'subtotal'        => $isTrial ? 0 : $plan->price,
            'tax'             => 0,
            'total'           => $isTrial ? 0 : $plan->price,
            'currency'        => $plan->currency,
            'status'          => 'paid',
            'paid_date'       => Carbon::now(),
            'line_items'      => [
                ['description' => $plan->name . ' Plan (' . ($isTrial ? $plan->trial_days : $plan->duration_days) . ' days)', 'amount' => $isTrial ? 0 : $plan->price]
            ],
        ]);

        // Notify subscriber of successful payment and activation
        try {
            $user->notify(new \App\Notifications\PaymentSuccessNotification([
                'title' => 'Payment Successful',
                'message' => 'Your subscription payment of ' . $plan->currency . ' ' . number_format($plan->price, 2) . ' was successful.',
            ]));
        } catch (\Exception $e) {}

        $profile = $user->subscriberProfile;

        // Notify super admin and admin of new subscription payment
        try {
            $admins = \App\Models\User::role(['Super Admin', 'Admin'])->get();
            if ($admins->isNotEmpty()) {
                \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\PaymentSuccessNotification([
                    'title' => 'New B2B Subscription Payment',
                    'message' => $user->name . ' (' . ($profile->company_name ?? '') . ') has subscribed to ' . $plan->name . ' plan.',
                ]));
            }
        } catch (\Exception $e) {}

        if ($profile && $profile->status === 'pending') {
            $profile->update([
                'status' => 'approved',
                'store_status' => 'live',
                'is_verified' => true,
            ]);
        }

        return redirect()->route('subscriber.subscription.index')
            ->with('success', '🎉 Subscription activated! Welcome to the ' . $plan->name . ' plan.');
    }

    public function createRazorpayOrder(Request $request, SubscriptionPlan $plan)
    {
        if (!$plan->is_active) {
            return response()->json(['error' => 'Selected subscription plan is inactive.'], 400);
        }

        $key = config('services.razorpay.key');
        $secret = config('services.razorpay.secret');

        if (empty($key) || empty($secret) || $key === 'rzp_test_change_me' || $secret === 'change_me_secret') {
            return response()->json(['error' => 'Razorpay credentials are not configured yet. Please contact the administrator.'], 400);
        }

        // Amount in paise (multiply by 100)
        $amount = (int) round($plan->price * 100);

        try {
            $response = Http::withBasicAuth($key, $secret)
                ->post('https://api.razorpay.com/v1/orders', [
                    'amount' => $amount,
                    'currency' => 'INR',
                    'receipt' => 'rcpt_' . Str::random(10),
                ]);

            if ($response->failed()) {
                Log::error('Razorpay Order Creation Failed: ' . $response->body());
                $errorData = $response->json();
                $errorMessage = $errorData['error']['description'] ?? 'Unable to initiate transaction with Razorpay. Try again later.';
                return response()->json(['error' => $errorMessage], 400);
            }

            $order = $response->json();

            return response()->json([
                'id' => $order['id'],
                'amount' => $order['amount'],
                'currency' => $order['currency'],
                'key' => $key,
                'user' => [
                    'name' => auth()->user()->name,
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
        $orderId = $request->input('razorpay_order_id');
        $signature = $request->input('razorpay_signature');

        $key = config('services.razorpay.key');
        $secret = config('services.razorpay.secret');

        // Verify the signature
        $expectedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $secret);

        $key = config('services.razorpay.key');
        $isTest = str_starts_with($key, 'rzp_test');

        if ($expectedSignature !== $signature) {
            Log::warning('Razorpay payment signature mismatch.', [
                'expected' => $expectedSignature,
                'received' => $signature,
                'order_id' => $orderId,
                'payment_id' => $paymentId,
            ]);

            // Save a failed payment record
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
                'gateway_response'     => [
                    'error' => 'Signature mismatch during verification'
                ],
                'notes'                => $isTest ? 'Razorpay Sandbox Payment Failed: Signature mismatch verification' : 'Razorpay Live Payment Failed: Signature mismatch verification',
            ]);

            return redirect()->route('subscriber.subscription.checkout', $plan->id)
                ->with('error', '❌ Secure verification failed! Signature mismatch. Your payment was not processed.');
        }

        // Signature matches! Let's activate subscription!
        $user = auth()->user();

        // Create successful payment record
        $payment = Payment::create([
            'user_id'              => $user->id,
            'subscription_plan_id' => $plan->id,
            'transaction_id'       => $paymentId,
            'gateway'              => 'razorpay',
            'gateway_payment_id'   => $paymentId,
            'gateway_order_id'     => $orderId,
            'amount'               => $plan->price,
            'currency'             => 'INR',
            'status'               => 'success',
            'paid_at'              => Carbon::now(),
            'gateway_response'     => $request->all(),
            'notes'                => $isTest ? 'Razorpay Sandbox Payment Verified Securely' : 'Razorpay Live Production Payment Verified Securely',
        ]);

        // Cancel old subscriptions
        Subscription::where('user_id', $user->id)
            ->whereIn('status', ['active', 'trial'])
            ->update(['status' => 'cancelled', 'cancelled_at' => now()]);

        // Create new subscription
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
            'subtotal'        => $plan->price,
            'tax'             => 0,
            'total'           => $plan->price,
            'currency'        => 'INR',
            'status'          => 'paid',
            'paid_date'       => Carbon::now(),
            'line_items'      => [
                ['description' => $plan->name . ' Plan (' . $plan->duration_days . ' days)', 'amount' => $plan->price]
            ],
        ]);

        // Notifications
        try {
            $user->notify(new \App\Notifications\PaymentSuccessNotification([
                'title' => 'Payment Successful',
                'message' => 'Your subscription payment of INR ' . number_format($plan->price, 2) . ' was successful.',
            ]));
        } catch (\Exception $e) {
            Log::error('Notification failed: ' . $e->getMessage());
        }

        $profile = $user->subscriberProfile;

        try {
            $admins = \App\Models\User::role(['Super Admin', 'Admin'])->get();
            if ($admins->isNotEmpty()) {
                \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\PaymentSuccessNotification([
                    'title' => 'New B2B Subscription Payment (Razorpay)',
                    'message' => $user->name . ' (' . ($profile->company_name ?? '') . ') has subscribed to ' . $plan->name . ' plan.',
                ]));
            }
        } catch (\Exception $e) {
            Log::error('Admin notification failed: ' . $e->getMessage());
        }

        if ($profile && $profile->status === 'pending') {
            $profile->update([
                'status' => 'approved',
                'store_status' => 'live',
                'is_verified' => true,
            ]);
        }

        return redirect()->route('subscriber.subscription.index')
            ->with('success', '🎉 Subscription activated! Welcome to the ' . $plan->name . ' plan.');
    }

    public function invoiceDownload(Invoice $invoice)
    {
        if ($invoice->user_id !== auth()->id()) abort(403);
        $invoice->load('subscription.plan', 'payment');
        $user = auth()->user();
        $profile = $user->subscriberProfile;
        return view('subscriber-panel.subscription.invoice-pdf', compact('invoice', 'user', 'profile'));
    }
}
