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
            ->where('is_trial', false)
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

        // Create payment record (dummy/sandbox success)
        $payment = Payment::create([
            'user_id'              => $user->id,
            'subscription_plan_id' => $plan->id,
            'transaction_id'       => 'TXN-' . strtoupper(Str::random(12)),
            'gateway'              => $gateway,
            'gateway_payment_id'   => $request->input('gateway_payment_id', 'pay_' . Str::random(14)),
            'gateway_order_id'     => $request->input('gateway_order_id', 'order_' . Str::random(14)),
            'amount'               => $plan->price,
            'currency'             => $plan->currency,
            'status'               => 'success',
            'paid_at'              => Carbon::now(),
            'gateway_response'     => [
                'message' => 'Simulated ' . ucfirst($gateway) . ' sandbox payment success',
                'mode' => 'test',
                'email' => $request->input('paypal_email') ?? $user->email,
            ],
            'notes'                => 'Demo payment - Test mode (' . ucfirst($gateway) . ')',
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
        $invoice = Invoice::create([
            'user_id'         => $user->id,
            'subscription_id' => $subscription->id,
            'payment_id'      => $payment->id,
            'invoice_number'  => Invoice::generateNumber(),
            'subtotal'        => $plan->price,
            'tax'             => 0,
            'total'           => $plan->price,
            'currency'        => $plan->currency,
            'status'          => 'paid',
            'paid_date'       => Carbon::now(),
            'line_items'      => [
                ['description' => $plan->name . ' Plan (' . $plan->duration_days . ' days)', 'amount' => $plan->price]
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

        // Notify super admin of new subscription payment
        try {
            $superAdmins = \App\Models\User::role('Super Admin')->get();
            if ($superAdmins->isNotEmpty()) {
                \Illuminate\Support\Facades\Notification::send($superAdmins, new \App\Notifications\PaymentSuccessNotification([
                    'title' => 'New B2B Subscription Payment',
                    'message' => $user->name . ' (' . ($profile->company_name ?? '') . ') has subscribed to ' . $plan->name . ' plan.',
                ]));
            }
        } catch (\Exception $e) {}

        if ($profile && $profile->status === 'pending') {
            return redirect()->route('subscriber.pending-approval')
                ->with('success', '🎉 Payment successful! Your store is now pending Super Admin approval.');
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
