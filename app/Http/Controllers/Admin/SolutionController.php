<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Solution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SolutionController extends Controller
{
    public function index()
    {
        $solutions = Solution::latest()->get();
        return view('admin.solutions.index', compact('solutions'));
    }

    public function create()
    {
        return view('admin.solutions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:solutions,name',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'icon' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
        ]);

        File::ensureDirectoryExists(public_path('uploads/solutions'));

        $imageName = null;
        $iconName = null;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_img_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/solutions'), $imageName);
        }

        if ($request->hasFile('icon')) {
            $icon = $request->file('icon');
            $iconName = time() . '_icon_' . uniqid() . '.' . $icon->getClientOriginalExtension();
            $icon->move(public_path('uploads/solutions'), $iconName);
        }

        Solution::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'image' => $imageName,
            'icon' => $iconName,
            'short_description' => $request->short_description,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.solutions.index')->with('success', 'Solution Created Successfully');
    }

    public function show(string $id)
    {
        $solution = Solution::findOrFail($id);
        return view('admin.solutions.show', compact('solution'));
    }

    public function edit(string $id)
    {
        $solution = Solution::findOrFail($id);
        return view('admin.solutions.edit', compact('solution'));
    }

    public function update(Request $request, string $id)
    {
        $solution = Solution::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:solutions,name,' . $solution->id,
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'icon' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
        ]);

        File::ensureDirectoryExists(public_path('uploads/solutions'));

        $imageName = $solution->image;
        $iconName = $solution->icon;

        if ($request->hasFile('image')) {
            if ($solution->image && file_exists(public_path('uploads/solutions/' . $solution->image))) {
                unlink(public_path('uploads/solutions/' . $solution->image));
            }
            $image = $request->file('image');
            $imageName = time() . '_img_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/solutions'), $imageName);
        }

        if ($request->hasFile('icon')) {
            if ($solution->icon && file_exists(public_path('uploads/solutions/' . $solution->icon))) {
                unlink(public_path('uploads/solutions/' . $solution->icon));
            }
            $icon = $request->file('icon');
            $iconName = time() . '_icon_' . uniqid() . '.' . $icon->getClientOriginalExtension();
            $icon->move(public_path('uploads/solutions'), $iconName);
        }

        $solution->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'image' => $imageName,
            'icon' => $iconName,
            'short_description' => $request->short_description,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.solutions.index')->with('success', 'Solution Updated Successfully');
    }

    public function destroy(string $id)
    {
        $solution = Solution::findOrFail($id);

        if ($solution->image && file_exists(public_path('uploads/solutions/'.$solution->image))) {
            unlink(public_path('uploads/solutions/'.$solution->image));
        }

        if ($solution->icon && file_exists(public_path('uploads/solutions/'.$solution->icon))) {
            unlink(public_path('uploads/solutions/'.$solution->icon));
        }

        $solution->delete();

        return redirect()->route('admin.solutions.index')->with('success', 'Solution Deleted Successfully');
    }
}
