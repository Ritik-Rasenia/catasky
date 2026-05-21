<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use Illuminate\Http\Request;

class EnquiryController extends Controller
{
    public function index()
    {
        $enquiries = Enquiry::with(['product', 'brand'])->latest()->paginate(20);
        return view('admin.enquiry.index', compact('enquiries'));
    }

    public function show($id)
    {
        $enquiry = Enquiry::with(['product', 'brand'])->findOrFail($id);
        
        // Mark as read
        if (!$enquiry->is_read) {
            $enquiry->is_read = true;
            $enquiry->save();
        }

        return view('admin.enquiry.show', compact('enquiry'));
    }

    public function destroy($id)
    {
        $enquiry = Enquiry::findOrFail($id);
        $enquiry->delete();

        return redirect()->route('admin.enquiries.index')->with('success', 'Enquiry deleted successfully.');
    }

    public function markAsRead($id)
    {
        $enquiry = Enquiry::findOrFail($id);
        $enquiry->is_read = true;
        $enquiry->save();

        return back()->with('success', 'Enquiry marked as read.');
    }
}
