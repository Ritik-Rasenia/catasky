<?php

namespace App\Http\Controllers\Subscriber;

use App\Http\Controllers\Controller;
use App\Models\SubscriberProfile;
use App\Models\SubscriberPdfTemplate;
use App\Models\SubscriberActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        $profile = $user->subscriberProfile ?? new SubscriberProfile(['user_id' => $user->id]);
        $pdfTemplate = SubscriberPdfTemplate::where('user_id', $user->id)
            ->where('is_default', true)
            ->first() ?? new SubscriberPdfTemplate();
        $subscription = $user->activeSubscription();
        $invoices = $user->invoices()->latest()->get();

        return view('subscriber-panel.profile.edit', compact('user', 'profile', 'pdfTemplate', 'subscription', 'invoices'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name'            => 'required|string|max:255',
            'company_name'    => 'required|string|max:255',
            'phone'           => 'nullable|string|max:20',
            'whatsapp_number' => 'nullable|string|max:20',
            'website'         => 'nullable|url|max:255',
            'logo'            => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
            'banner'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'profile_image'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'primary_color'   => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
        ]);

        $userData = ['name' => $request->name];

        if ($request->hasFile('profile_image')) {
            $avatarName = Str::random(20) . '.' . $request->file('profile_image')->getClientOriginalExtension();
            $request->file('profile_image')->move(public_path('uploads/profile'), $avatarName);
            $userData['profile_image'] = $avatarName;
        }

        $user->update($userData);

        $profileData = $request->only([
            'company_name', 'phone', 'whatsapp_number', 'website',
            'address', 'city', 'state', 'country', 'pincode',
            'gst_number', 'bio', 'email_for_inquiries',
            'primary_color', 'secondary_color',
        ]);

        if ($request->hasFile('logo')) {
            $filename = Str::random(20) . '.' . $request->file('logo')->getClientOriginalExtension();
            $request->file('logo')->move(public_path('uploads/subscriber-logos'), $filename);
            $profileData['logo'] = $filename;
        }

        if ($request->hasFile('banner')) {
            $bannerFilename = Str::random(20) . '.' . $request->file('banner')->getClientOriginalExtension();
            $request->file('banner')->move(public_path('uploads/subscriber-banners'), $bannerFilename);
            $profileData['banner'] = $bannerFilename;
        }

        $currentStoreStatus = 'live';

        $profile = SubscriberProfile::updateOrCreate(
            ['user_id' => $user->id],
            array_merge($profileData, [
                'status' => 'approved',
                'store_status' => 'live',
            ])
        );

        SubscriberActivityLog::log('updated', 'Updated profile', $profile);

        return redirect()->route('subscriber.profile.edit')->with('success', 'Profile & store configuration submitted successfully!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|string|min:8|max:20|alpha_num|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password updated successfully!');
    }

    public function updatePdfTemplate(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'brand_color'  => 'nullable|string|max:7',
            'accent_color' => 'nullable|string|max:7',
            'layout'       => 'nullable|in:grid,list,detailed',
            'paper_size'   => 'nullable|in:A4,A3,Letter',
        ]);

        SubscriberPdfTemplate::updateOrCreate(
            ['user_id' => $user->id, 'is_default' => true],
            [
                'name'              => $request->template_name ?? 'Default Template',
                'show_logo'         => $request->boolean('show_logo', true),
                'show_watermark'    => $request->boolean('show_watermark'),
                'watermark_text'    => $request->watermark_text,
                'show_qr_code'      => $request->boolean('show_qr_code', true),
                'show_page_numbers' => $request->boolean('show_page_numbers', true),
                'brand_color'       => $request->brand_color ?? '#4F46E5',
                'accent_color'      => $request->accent_color ?? '#7C3AED',
                'layout'            => $request->layout ?? 'grid',
                'paper_size'        => $request->paper_size ?? 'A4',
                'orientation'       => $request->orientation ?? 'portrait',
                'header_text'       => $request->header_text,
                'footer_text'       => $request->footer_text,
            ]
        );

        return back()->with('success', 'PDF template settings saved!');
    }
}
