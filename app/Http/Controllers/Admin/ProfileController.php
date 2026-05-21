<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('admin.profile.edit', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,webp',
        ]);

        $imageName = $user->profile_image;

        if($request->hasFile('profile_image')){
            if($user->profile_image && file_exists(public_path('uploads/profile/'.$user->profile_image))){
                unlink(public_path('uploads/profile/'.$user->profile_image));
            }

            $image = $request->file('profile_image');
            $imageName = time().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('uploads/profile'), $imageName);
        }

        $user->update([
            'name'          => $request->name,
            'email'         => $request->email,
            'profile_image' => $imageName,
        ]);

        return redirect()->back()->with('success', 'Profile Updated Successfully');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back()->with('success', 'Password Updated Successfully');
    }
}
