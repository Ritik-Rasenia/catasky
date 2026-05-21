<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Login Page
    |--------------------------------------------------------------------------
    */

    public function login()
    {
        return view('login');
    }


    /*
    |--------------------------------------------------------------------------
    | Login Submit
    |--------------------------------------------------------------------------
    */

    public function loginSubmit(Request $request)
    {

        // Validate Request
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);


        // Check User Exists
        $user = User::where('email', $request->email)->first();

        if (!$user) {

            return back()
                ->withErrors([
                    'email' => 'Email not found',
                ])
                ->withInput();
        }


        // Check Password Match
        if (!Hash::check($request->password, $user->password)) {

            return back()
                ->withErrors([
                    'password' => 'Invalid password',
                ])
                ->withInput();
        }


        // Login User
        Auth::login($user, $request->remember);


        // Regenerate Session
        $request->session()->regenerate();


        // Redirect Dashboard
        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Login Successfully');
    }


    /*
    |--------------------------------------------------------------------------
    | Register Page
    |--------------------------------------------------------------------------
    */

    public function register()
    {
        return view('register');
    }


    /*
    |--------------------------------------------------------------------------
    | Register Submit
    |--------------------------------------------------------------------------
    */

    public function registerSubmit(Request $request)
    {

        // Validate Request
        $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|min:6|confirmed',
        ]);


        // Create User
        User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
        ]);


        // Redirect Login
        return redirect()
            ->route('login')
            ->with('success', 'Registration Successful');
    }


    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    public function logout(Request $request)
    {

        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Logout Successfully');
    }
}