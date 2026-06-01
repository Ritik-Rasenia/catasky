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
            'password'     => 'required|string|min:8|max:20|alpha_num|confirmed',
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

        // Generate 6-digit verification code
        $otp = (string)rand(100000, 999999);

        // Put registration details in session until verified
        $request->session()->put('registration_data', [
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'company_name' => $request->company_name,
            'phone' => $request->phone,
            'whatsapp_number' => $request->whatsapp_number,
            'selected_plan' => $request->input('selected_plan', 'business'),
        ]);
        $request->session()->put('registration_otp', $otp);

        // Dispatch real registration verification email
        try {
            \Illuminate\Support\Facades\Notification::route('mail', $request->email)
                ->notify(new \App\Notifications\OtpVerificationNotification($otp));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Registration OTP dispatch failed: ' . $e->getMessage());
        }

        return redirect()->route('subscriber.verify-otp')
            ->with('success', 'A 6-digit verification code has been dispatched to your email.');
    }

    public function showOtpForm(Request $request)
    {
        if (!$request->session()->has('registration_data') || !$request->session()->has('registration_otp')) {
            return redirect()->route('subscriber.register')
                ->with('error', 'Please fill the registration form first.');
        }

        $email = $request->session()->get('registration_data')['email'];

        return view('subscriber-panel.auth.otp', compact('email'));
    }

    public function resendOtp(Request $request)
    {
        if (!$request->session()->has('registration_data')) {
            return redirect()->route('subscriber.register')
                ->with('error', 'Please fill the registration form first.');
        }

        $regData = $request->session()->get('registration_data');
        $otp = (string)rand(100000, 999999);
        $request->session()->put('registration_otp', $otp);

        try {
            \Illuminate\Support\Facades\Notification::route('mail', $regData['email'])
                ->notify(new \App\Notifications\OtpVerificationNotification($otp));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Resend OTP dispatch failed: ' . $e->getMessage());
        }

        return redirect()->route('subscriber.verify-otp')
            ->with('success', 'A new 6-digit verification code has been dispatched to your email.');
    }

    public function verifyOtp(Request $request)
    {
        if (!$request->session()->has('registration_data') || !$request->session()->has('registration_otp')) {
            return redirect()->route('subscriber.register')
                ->with('error', 'Please fill the registration form first.');
        }

        $request->validate([
            'otp' => 'required|string|size:6'
        ]);

        $sessionOtp = $request->session()->get('registration_otp');

        if ($request->otp !== $sessionOtp) {
            return back()->withErrors(['otp' => 'The entered verification code is incorrect. Please try again.'])->withInput();
        }

        $regData = $request->session()->get('registration_data');

        // Create user
        $user = User::create([
            'name'     => $regData['name'],
            'email'    => $regData['email'],
            'password' => Hash::make($regData['password']),
        ]);

        // Assign Subscriber role (strictly synchronize to prevent any role leaks)
        $subscriberRole = Role::firstOrCreate(['name' => 'Subscriber', 'guard_name' => 'web']);
        $user->syncRoles([$subscriberRole]);

        // Create subscriber profile (Pending Compliance Approval default)
        $companySlug = Str::slug($regData['company_name']);
        $slugExists = SubscriberProfile::where('company_slug', $companySlug)->exists();
        if ($slugExists) {
            $companySlug .= '-' . Str::random(4);
        }

        $profile = SubscriberProfile::create([
            'user_id'          => $user->id,
            'company_name'     => $regData['company_name'],
            'company_slug'     => $companySlug,
            'phone'            => $regData['phone'],
            'whatsapp_number'  => $regData['whatsapp_number'],
            'status'           => 'approved', // Auto-approved
            'store_status'     => 'live',     // Auto-approved
            'is_verified'      => true,       // Verification completed
        ]);

        // Create default PDF template
        SubscriberPdfTemplate::create([
            'user_id'     => $user->id,
            'name'        => 'Default Template',
            'is_default'  => true,
        ]);

        // Notify Super Admins of new subscriber registration
        try {
            $superAdmins = User::role('Super Admin')->get();
            if ($superAdmins->isNotEmpty()) {
                \Illuminate\Support\Facades\Notification::send($superAdmins, new \App\Notifications\SubscriberRegistrationNotification([
                    'title' => 'New B2B Registration',
                    'message' => 'New B2B registration request from ' . $regData['name'] . ' (' . $regData['company_name'] . ').',
                ]));
            }
        } catch (\Exception $e) {}

        // Clean session verification data
        $request->session()->forget(['registration_data', 'registration_otp']);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('subscriber.dashboard')
            ->with('success', '🎉 Verification successful! Your account and B2B storefront have been automatically approved and are now live.');
    }

    public function showForgotForm()
    {
        return view('subscriber-panel.auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ], [
            'email.exists' => 'This email is not registered in our system.'
        ]);

        $user = User::where('email', $request->email)->first();
        if ($user && $user->hasRole('Subscriber')) {
            $token = Str::random(60);
            
            // Save token to password_reset_tokens table
            \Illuminate\Support\Facades\DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $request->email],
                [
                    'token' => Hash::make($token),
                    'created_at' => now()
                ]
            );

            try {
                \Illuminate\Support\Facades\Notification::route('mail', $request->email)
                    ->notify(new \App\Notifications\ForgotPasswordNotification($token, $request->email));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Forgot password link dispatch failed: ' . $e->getMessage());
            }
        }

        return back()->with('status', 'If this email is registered, you will receive a password reset link shortly.');
    }

    public function showResetPasswordForm($token, Request $request)
    {
        $email = $request->query('email');
        
        $resetRecord = \Illuminate\Support\Facades\DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$resetRecord || !Hash::check($token, $resetRecord->token)) {
            return redirect()->route('subscriber.forgot')
                ->withErrors(['email' => 'This password reset link is invalid or has expired. Please request a new one.']);
        }

        // Expire link after 1 hour (3600 seconds)
        if (Carbon::parse($resetRecord->created_at)->addSeconds(3600)->isPast()) {
            \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $email)->delete();
            return redirect()->route('subscriber.forgot')
                ->withErrors(['email' => 'This password reset link has expired. Please request a new one.']);
        }

        return view('subscriber-panel.auth.reset-password', compact('token', 'email'));
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'                 => 'required',
            'email'                 => 'required|email|exists:users,email',
            'password'              => 'required|string|min:8|max:20|alpha_num|confirmed',
        ], [
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        $resetRecord = \Illuminate\Support\Facades\DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord || !Hash::check($request->token, $resetRecord->token)) {
            return redirect()->route('subscriber.forgot')
                ->withErrors(['email' => 'This password reset link is invalid or has expired. Please request a new one.']);
        }

        if (Carbon::parse($resetRecord->created_at)->addSeconds(3600)->isPast()) {
            \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return redirect()->route('subscriber.forgot')
                ->withErrors(['email' => 'This password reset link has expired. Please request a new one.']);
        }

        // Update user password
        $user = User::where('email', $request->email)->first();
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Delete used token
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('subscriber.login')
            ->with('success', 'Your password has been successfully reset. You can now log in with your new password.');
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
