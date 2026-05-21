<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subcategory;
use App\Models\Category;
use Illuminate\Support\Str;

class SubcategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $subcategories = Subcategory::with('category')->latest()->get();

        return view('admin.subcategories.index', compact('subcategories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::where('status', 1)->get();
        return view('admin.subcategories.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|unique:subcategories,name',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp',
            'status'      => 'required',
        ]);

        $imageName = null;

        if($request->hasFile('image')){
            $image = $request->file('image');
            $imageName = time().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('uploads/subcategories'), $imageName);
        }

        Subcategory::create([
            'category_id' => $request->category_id,
            'name'        => $request->name,
            'slug'        => Str::slug($request->name),
            'image'       => $imageName,
            'status'      => $request->status,
        ]);

        return redirect()
            ->route('admin.subcategories.index')
            ->with('success', 'Subcategory Created Successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $subcategory = Subcategory::findOrFail($id);
        return view('admin.subcategories.show', compact('subcategory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $subcategory = Subcategory::findOrFail($id);
        $categories = Category::where('status', 1)->get();
        return view('admin.subcategories.edit', compact('subcategory', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $subcategory = Subcategory::findOrFail($id);

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|unique:subcategories,name,'.$subcategory->id,
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp',
            'status'      => 'required',
        ]);

        $imageName = $subcategory->image;

        if($request->hasFile('image')){
            if($subcategory->image && file_exists(public_path('uploads/subcategories/'.$subcategory->image))){
                unlink(public_path('uploads/subcategories/'.$subcategory->image));
            }

            $image = $request->file('image');
            $imageName = time().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('uploads/subcategories'), $imageName);
        }

        $subcategory->update([
            'category_id' => $request->category_id,
            'name'        => $request->name,
            'slug'        => Str::slug($request->name),
            'image'       => $imageName,
            'status'      => $request->status,
        ]);

        return redirect()
            ->route('admin.subcategories.index')
            ->with('success', 'Subcategory Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $subcategory = Subcategory::findOrFail($id);

        if($subcategory->image && file_exists(public_path('uploads/subcategories/'.$subcategory->image))){
            unlink(public_path('uploads/subcategories/'.$subcategory->image));
        }

        $subcategory->delete();

        return redirect()
            ->route('admin.subcategories.index')
            ->with('success', 'Subcategory Deleted Successfully');
    }

    /**
     * AJAX endpoint to get child categories for a subcategory
     */
    public function getChildcategories($subcategoryId)
    {
        $childcategories = \App\Models\ChildCategory::where('subcategory_id', $subcategoryId)
                                                    ->where('status', 1)
                                                    ->get();
        return response()->json($childcategories);
    }
}
