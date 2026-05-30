<?php

namespace App\Http\Controllers\Subscriber;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::where('subscriber_id', auth()->id())->latest()->get();

        return view('subscriber-panel.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('subscriber-panel.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                Rule::unique('categories', 'name')->where(function ($query) {
                    return $query->where('subscriber_id', auth()->id())->whereNull('deleted_at');
                })
            ],
            'image'  => 'nullable|image|mimes:jpg,jpeg,png,webp',
            'status' => 'required',
        ]);

        $imageName = null;

        if($request->hasFile('image')){
            $image = $request->file('image');
            $imageName = time().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('uploads/categories'), $imageName);
        }

        Category::create([
            'name'          => $request->name,
            'slug'          => Str::slug($request->name),
            'image'         => $imageName,
            'status'        => $request->status,
            'subscriber_id' => auth()->id(),
        ]);

        return redirect()
            ->route('subscriber.categories.index')
            ->with('success', 'Category Created Successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = Category::where('subscriber_id', auth()->id())->findOrFail($id);
        return view('subscriber-panel.categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $category = Category::where('subscriber_id', auth()->id())->findOrFail($id);
        return view('subscriber-panel.categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $category = Category::where('subscriber_id', auth()->id())->findOrFail($id);

        $request->validate([
            'name' => [
                'required',
                Rule::unique('categories', 'name')
                    ->ignore($category->id)
                    ->where(function ($query) {
                        return $query->where('subscriber_id', auth()->id())->whereNull('deleted_at');
                    })
            ],
            'image'  => 'nullable|image|mimes:jpg,jpeg,png,webp',
            'status' => 'required',
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
            ->route('subscriber.categories.index')
            ->with('success', 'Category Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = Category::where('subscriber_id', auth()->id())->findOrFail($id);

        if($category->image && file_exists(public_path('uploads/categories/'.$category->image))){
            unlink(public_path('uploads/categories/'.$category->image));
        }

        $category->delete();

        return redirect()
            ->route('subscriber.categories.index')
            ->with('success', 'Category Deleted Successfully');
    }
}
