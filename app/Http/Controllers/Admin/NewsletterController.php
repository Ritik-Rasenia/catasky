<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $newsletters = \App\Models\Newsletter::latest()->paginate(20);
        return view('admin.newsletters.index', compact('newsletters'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $newsletter = \App\Models\Newsletter::findOrFail($id);
        $newsletter->delete();
        return redirect()->back()->with('success', 'Subscriber deleted successfully.');
    }
}
