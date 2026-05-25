<?php

namespace App\Http\Controllers\Subscriber\Auth;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\Subscription;
use App\Models\SubscriberProfile;
use App\Models\SubscriberPdfTemplate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

class SubscriberAuthController extends Controller
{
    public function showLoginForm()
    {
        if (auth()->check() && auth()->user()->hasRole('Subscriber')) {
            $profile = auth()->user()->subscriberProfile;
            return ($profile && $profile->isApproved())
                ? redirect()->route('subscriber.dashboard')
                : redirect()->route('subscriber.pending-approval');
        }
        return view('subscriber-panel.auth.login');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            if (!$user->hasRole('Subscriber')) {
                Auth::logout();
                return back()->withErrors(['email' => 'This account does not have subscriber access.'])->withInput();
            }

            $request->session()->regenerate();

            $profile = $user->subscriberProfile;
            if (!$profile || !$profile->isApproved()) {
                return redirect()->route('subscriber.pending-approval');
            }

            return redirect()->route('subscriber.dashboard')
                ->with('success', 'Welcome back, ' . $user->name . '!');
        }

        return back()->withErrors(['email' => 'Invalid email or password.'])->withInput();
    }

    public function showRegisterForm(Request $request)
    {
        if (auth()->check() && auth()->user()->hasRole('Subscriber')) {
            $profile = auth()->user()->subscriberProfile;
            return ($profile && $profile->isApproved())
                ? redirect()->route('subscriber.dashboard')
                : redirect()->route('subscriber.pending-approval');
        }
        $selectedPlan = $request->query('plan');
        return view('subscriber-panel.auth.register', compact('selectedPlan'));
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'password'     => 'required|min:8|confirmed',
            'company_name' => 'required|string|max:255',
            'phone'        => 'nullable|string|max:20',
            'whatsapp_number' => 'nullable|string|max:20',
        ], [
            'password.confirmed' => 'Password confirmation does not match.',
            'email.unique' => 'This email is already registered.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Create user
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Assign Subscriber role
        $subscriberRole = Role::firstOrCreate(['name' => 'Subscriber', 'guard_name' => 'web']);
        $user->assignRole($subscriberRole);

        // Create subscriber profile (Pending Approval default)
        $companySlug = Str::slug($request->company_name);
        $slugExists = SubscriberProfile::where('company_slug', $companySlug)->exists();
        if ($slugExists) {
            $companySlug .= '-' . Str::random(4);
        }

        $profile = SubscriberProfile::create([
            'user_id'          => $user->id,
            'company_name'     => $request->company_name,
            'company_slug'     => $companySlug,
            'phone'            => $request->phone,
            'whatsapp_number'  => $request->whatsapp_number,
            'status'           => 'pending', // Pending approval by admin
        ]);

        // Create default PDF template
        SubscriberPdfTemplate::create([
            'user_id'     => $user->id,
            'name'        => 'Default Template',
            'is_default'  => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('subscriber.subscription.plans')
            ->with('success', 'Welcome to Catasky. Please complete your subscription payment, then Super Admin will approve your account.');
    }

    public function showForgotForm()
    {
        return view('subscriber-panel.auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Basic implementation - in production use Password::sendResetLink
        $user = User::where('email', $request->email)->first();
        if ($user && $user->hasRole('Subscriber')) {
            // TODO: Send reset email
        }

        return back()->with('success', 'If this email is registered, you will receive a password reset link shortly.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('subscriber.login')
            ->with('success', 'You have been logged out.');
    }
}
