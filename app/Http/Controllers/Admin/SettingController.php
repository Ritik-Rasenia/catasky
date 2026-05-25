<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function index()
    {
        $setting = Setting::first();
        if (!$setting) {
            $setting = Setting::create([]);
        }

        return view('admin.settings.index', compact('setting'));
    }

    public function update(Request $request)
    {
        $setting = Setting::firstOrCreate([]);

        $request->validate([
            'site_title' => 'nullable|string|max:255',
            'site_description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
            'footer_logo' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
            'favicon' => 'nullable|image|mimes:jpg,jpeg,png,ico,webp|max:1024',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'admin_email' => 'nullable|email|max:255',
            'facebook' => 'nullable|url|max:255',
            'twitter' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'linkedin' => 'nullable|url|max:255',
            'youtube' => 'nullable|url|max:255',
            'primary_color' => 'nullable|string|max:10',
            'secondary_color' => 'nullable|string|max:10',
            'font_family' => 'nullable|string|max:50',
            'pdf_cover_style' => 'nullable|string|max:50',
            'watermark' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:1024',
            'meta_keywords' => 'nullable|string',
        ]);

        $logoName = $setting->logo;
        $footerLogoName = $setting->footer_logo;
        $faviconName = $setting->favicon;
        $watermarkName = $setting->watermark;

        // Handle Logo
        if ($request->hasFile('logo')) {
            if ($setting->logo && file_exists(public_path('uploads/settings/'.$setting->logo))) {
                unlink(public_path('uploads/settings/'.$setting->logo));
            }
            $logo = $request->file('logo');
            $logoName = time() . '_logo.' . $logo->getClientOriginalExtension();
            $logo->move(public_path('uploads/settings'), $logoName);
        }
        
        // Handle Footer Logo
        if ($request->hasFile('footer_logo')) {
            if ($setting->footer_logo && file_exists(public_path('uploads/settings/'.$setting->footer_logo))) {
                unlink(public_path('uploads/settings/'.$setting->footer_logo));
            }
            $footerLogo = $request->file('footer_logo');
            $footerLogoName = time() . '_footer_logo.' . $footerLogo->getClientOriginalExtension();
            $footerLogo->move(public_path('uploads/settings'), $footerLogoName);
        }

        // Handle Favicon
        if ($request->hasFile('favicon')) {
            if ($setting->favicon && file_exists(public_path('uploads/settings/'.$setting->favicon))) {
                unlink(public_path('uploads/settings/'.$setting->favicon));
            }
            $favicon = $request->file('favicon');
            $faviconName = time() . '_favicon.' . $favicon->getClientOriginalExtension();
            $favicon->move(public_path('uploads/settings'), $faviconName);
        }

        // Handle Watermark
        if ($request->hasFile('watermark')) {
            if ($setting->watermark && file_exists(public_path('uploads/settings/'.$setting->watermark))) {
                unlink(public_path('uploads/settings/'.$setting->watermark));
            }
            $watermark = $request->file('watermark');
            $watermarkName = time() . '_watermark.' . $watermark->getClientOriginalExtension();
            $watermark->move(public_path('uploads/settings'), $watermarkName);
        }

        $setting->update([
            'site_title' => $request->site_title,
            'site_description' => $request->site_description,
            'logo' => $logoName,
            'footer_logo' => $footerLogoName,
            'favicon' => $faviconName,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'admin_email' => $request->admin_email,
            'facebook' => $request->facebook,
            'twitter' => $request->twitter,
            'instagram' => $request->instagram,
            'linkedin' => $request->linkedin,
            'youtube' => $request->youtube,
            'primary_color' => $request->primary_color,
            'secondary_color' => $request->secondary_color,
            'font_family' => $request->font_family,
            'pdf_cover_style' => $request->pdf_cover_style,
            'watermark' => $watermarkName,
            'meta_keywords' => $request->meta_keywords,
        ]);

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Settings updated successfully.');
    }
}
