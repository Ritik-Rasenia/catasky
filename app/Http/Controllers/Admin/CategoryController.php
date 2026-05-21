<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::latest()->get(); // For DataTables, we can get all or paginate, DataTables client-side can handle all. Let's stick to get() or paginate(100). Let's do get() since it's client-side DT.

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|unique:categories,name',
            'image'     => 'nullable|image|mimes:jpg,jpeg,png,webp',
            'status'    => 'required',
        ]);

        $imageName = null;

        if($request->hasFile('image')){
            $image = $request->file('image');
            $imageName = time().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('uploads/categories'), $imageName);
        }

        Category::create([
            'name'      => $request->name,
            'slug'      => Str::slug($request->name),
            'image'     => $imageName,
            'status'    => $request->status,
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category Created Successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = Category::findOrFail($id);
        return view('admin.categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $category = Category::findOrFail($id);
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name'      => 'required|unique:categories,name,'.$category->id,
            'image'     => 'nullable|image|mimes:jpg,jpeg,png,webp',
            'status'    => 'required',
        ]);

        $imageName = $category->image;

        if($request->hasFile('image')){
            if($category->image && file_exists(public_path('uploads/categories/'.$category->image))){
                unlink(public_path('uploads/categories/'.$category->image));
            }

            $image = $request->file('image');
            $imageName = time().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('uploads/categories'), $imageName);
        }

        $category->update([
            'name'      => $request->name,
            'slug'      => Str::slug($request->name),
            'image'     => $imageName,
            'status'    => $request->status,
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);

        if($category->image && file_exists(public_path('uploads/categories/'.$category->image))){
            unlink(public_path('uploads/categories/'.$category->image));
        }

        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category Deleted Successfully');
    }

    /**
     * AJAX endpoint to get subcategories for a category
     */
    public function getSubcategories($categoryId)
    {
        $subcategories = \App\Models\Subcategory::where('category_id', $categoryId)
                                                ->where('status', 1)
                                                ->get();
        return response()->json($subcategories);
    }
}
