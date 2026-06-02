<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SubscriberProfile;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Payment;
use App\Models\SubscriberProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

class SubscriberController extends Controller
{
    public function index(Request $request)
    {
        abort_if(!auth()->user()->can('subscribers.manage'), 403, 'Unauthorized.');
        $query = User::role('Subscriber')->with(['subscriberProfile', 'subscription' => function($q) {
            $q->with('plan');
        }]);

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->status) {
            $query->whereHas('subscriberProfile', function($q) use ($request) {
                $q->where('status', $request->status);
            });
        }

        $subscribers = $query->latest()->paginate(20);
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();

        $stats = [
            'total'     => User::role('Subscriber')->count(),
            'active'    => SubscriberProfile::whereIn('status', ['approved', 'active'])->count(),
            'suspended' => SubscriberProfile::where('status', 'suspended')->count(),
            'trial'     => Subscription::where('status', 'trial')->count(),
        ];

        return view('admin.subscribers.index', compact('subscribers', 'plans', 'stats'));
    }

    public function show(User $user)
    {
        abort_if(!auth()->user()->can('subscribers.manage'), 403, 'Unauthorized.');
        if (!$user->hasRole('Subscriber')) abort(404);
        $user->load(['subscriberProfile', 'subscriptions.plan', 'payments.plan']);
        $productCount = SubscriberProduct::where('user_id', $user->id)->count();
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();
        return view('admin.subscribers.show', compact('user', 'productCount', 'plans'));
    }

    public function suspend(Request $request, User $user)
    {
        abort_if(!auth()->user()->can('subscribers.manage'), 403, 'Unauthorized.');
        if (!$user->hasRole('Subscriber')) abort(404);
        $request->validate(['reason' => 'nullable|string|max:500']);

        $profile = $user->subscriberProfile;
        if ($profile) {
            $profile->update([
                'status'            => 'suspended',
                'suspended_at'      => now(),
                'suspension_reason' => $request->reason,
            ]);
        }

        return back()->with('success', 'Subscriber account suspended.');
    }

    public function unsuspend(User $user)
    {
        abort_if(!auth()->user()->can('subscribers.manage'), 403, 'Unauthorized.');
        if (!$user->hasRole('Subscriber')) abort(404);
        $profile = $user->subscriberProfile;
        if ($profile) {
            $profile->update([
                'status'            => 'approved',
                'suspended_at'      => null,
                'suspension_reason' => null,
            ]);
        }
        return back()->with('success', 'Subscriber account reactivated.');
    }

    public function assignPlan(Request $request, User $user)
    {
        abort_if(!auth()->user()->can('subscribers.manage'), 403, 'Unauthorized.');
        if (!$user->hasRole('Subscriber')) abort(404);
        $request->validate([
            'plan_id'  => 'required|exists:subscription_plans,id',
            'duration' => 'nullable|integer|min:1|max:365',
        ]);

        $plan = SubscriptionPlan::findOrFail($request->plan_id);
        $days = $request->duration ?? $plan->duration_days;

        // Cancel existing active subscription
        Subscription::where('user_id', $user->id)
            ->whereIn('status', ['active', 'trial'])
            ->update(['status' => 'cancelled', 'cancelled_at' => now()]);

        Subscription::create([
            'user_id'              => $user->id,
            'subscription_plan_id' => $plan->id,
            'status'               => 'active',
            'starts_at'            => Carbon::now(),
            'ends_at'              => Carbon::now()->addDays($days),
        ]);

        return back()->with('success', 'Subscription plan assigned: ' . $plan->name);
    }

    public function destroy(User $user)
    {
        abort_if(!auth()->user()->can('subscribers.manage'), 403, 'Unauthorized.');
        if (!$user->hasRole('Subscriber')) abort(404);
        $user->delete();
        return redirect()->route('admin.subscribers.index')
            ->with('success', 'Subscriber account deleted.');
    }

    // Subscription Plans Management
    public function plans()
    {
        abort_if(!auth()->user()->can('subscribers.manage'), 403, 'Unauthorized.');
        $plans = SubscriptionPlan::withCount('subscriptions')->orderBy('sort_order')->get();
        return view('admin.subscribers.plans', compact('plans'));
    }

    public function storePlan(Request $request)
    {
        abort_if(!auth()->user()->can('subscribers.manage'), 403, 'Unauthorized.');
        $request->validate([
            'name'          => 'required|string|max:255',
            'price'         => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'product_limit' => 'required|integer|min:1',
        ]);

        SubscriptionPlan::create(array_merge($request->all(), [
            'slug'      => \Illuminate\Support\Str::slug($request->name),
            'features'  => $request->features ? explode("\n", $request->features) : null,
            'is_active' => $request->boolean('is_active', true),
        ]));

        return back()->with('success', 'Plan created!');
    }

    public function updatePlan(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        abort_if(!auth()->user()->can('subscribers.manage'), 403, 'Unauthorized.');
        $subscriptionPlan->update(array_merge($request->except('_token', '_method'), [
            'features'  => $request->features ? explode("\n", $request->features) : null,
            'is_active' => $request->boolean('is_active', true),
        ]));
        return back()->with('success', 'Plan updated!');
    }
}
