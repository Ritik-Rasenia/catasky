<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChildCategory;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Support\Str;

class ChildCategoryController extends Controller
{
    public function index()
    {
        $childcategories = ChildCategory::with('category', 'subcategory')->latest()->get();
        return view('admin.childcategories.index', compact('childcategories'));
    }

    public function create()
    {
        $categories = Category::where('status', 1)->get();
        return view('admin.childcategories.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id'    => 'required|exists:categories,id',
            'subcategory_id' => 'required|exists:subcategories,id',
            'name'           => 'required|unique:child_categories,name',
            'image'          => 'nullable|image|mimes:jpg,jpeg,png,webp',
            'status'         => 'required|in:0,1',
        ]);

        $imageName = null;

        if($request->hasFile('image')){
            $image = $request->file('image');
            $imageName = time().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('uploads/childcategories'), $imageName);
        }

        ChildCategory::create([
            'category_id'    => $request->category_id,
            'subcategory_id' => $request->subcategory_id,
            'name'           => $request->name,
            'slug'           => Str::slug($request->name),
            'image'          => $imageName,
            'status'         => $request->status,
        ]);

        return redirect()->route('admin.childcategories.index')->with('success', 'Child Category Created Successfully');
    }

    public function edit(string $id)
    {
        $childcategory = ChildCategory::findOrFail($id);
        $categories = Category::where('status', 1)->get();
        $subcategories = Subcategory::where('category_id', $childcategory->category_id)->where('status', 1)->get();
        return view('admin.childcategories.edit', compact('childcategory', 'categories', 'subcategories'));
    }

    public function update(Request $request, string $id)
    {
        $childcategory = ChildCategory::findOrFail($id);

        $request->validate([
            'category_id'    => 'required|exists:categories,id',
            'subcategory_id' => 'required|exists:subcategories,id',
            'name'           => 'required|unique:child_categories,name,'.$childcategory->id,
            'image'          => 'nullable|image|mimes:jpg,jpeg,png,webp',
            'status'         => 'required|in:0,1',
        ]);

        $imageName = $childcategory->image;

        if($request->hasFile('image')){
            if($childcategory->image && file_exists(public_path('uploads/childcategories/'.$childcategory->image))){
                unlink(public_path('uploads/childcategories/'.$childcategory->image));
            }

            $image = $request->file('image');
            $imageName = time().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('uploads/childcategories'), $imageName);
        }

        $childcategory->update([
            'category_id'    => $request->category_id,
            'subcategory_id' => $request->subcategory_id,
            'name'           => $request->name,
            'slug'           => Str::slug($request->name),
            'image'          => $imageName,
            'status'         => $request->status,
        ]);

        return redirect()->route('admin.childcategories.index')->with('success', 'Child Category Updated Successfully');
    }

    public function destroy(string $id)
    {
        $childcategory = ChildCategory::findOrFail($id);

        if($childcategory->image && file_exists(public_path('uploads/childcategories/'.$childcategory->image))){
            unlink(public_path('uploads/childcategories/'.$childcategory->image));
        }

        $childcategory->delete();

        return redirect()->route('admin.childcategories.index')->with('success', 'Child Category Deleted Successfully');
    }
}
