<?php

namespace App\Http\Controllers\Subscriber;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subcategory;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SubcategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $subcategories = Subcategory::where('subscriber_id', auth()->id())->with('category')->latest()->get();

        return view('subscriber-panel.subcategories.index', compact('subcategories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::where('subscriber_id', auth()->id())->where('status', 1)->get();
        return view('subscriber-panel.subcategories.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where('subscriber_id', auth()->id())
            ],
            'name' => [
                'required',
                Rule::unique('subcategories', 'name')->where(function ($query) {
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
            $image->move(public_path('uploads/subcategories'), $imageName);
        }

        Subcategory::create([
            'category_id'   => $request->category_id,
            'name'          => $request->name,
            'slug'          => Str::slug($request->name),
            'image'         => $imageName,
            'status'        => $request->status,
            'subscriber_id' => auth()->id(),
        ]);

        return redirect()
            ->route('subscriber.subcategories.index')
            ->with('success', 'Subcategory Created Successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $subcategory = Subcategory::where('subscriber_id', auth()->id())->findOrFail($id);
        return view('subscriber-panel.subcategories.show', compact('subcategory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $subcategory = Subcategory::where('subscriber_id', auth()->id())->findOrFail($id);
        $categories = Category::where('subscriber_id', auth()->id())->where('status', 1)->get();
        return view('subscriber-panel.subcategories.edit', compact('subcategory', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $subcategory = Subcategory::where('subscriber_id', auth()->id())->findOrFail($id);

        $request->validate([
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where('subscriber_id', auth()->id())
            ],
            'name' => [
                'required',
                Rule::unique('subcategories', 'name')
                    ->ignore($subcategory->id)
                    ->where(function ($query) {
                        return $query->where('subscriber_id', auth()->id())->whereNull('deleted_at');
                    })
            ],
            'image'  => 'nullable|image|mimes:jpg,jpeg,png,webp',
            'status' => 'required',
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
            ->route('subscriber.subcategories.index')
            ->with('success', 'Subcategory Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $subcategory = Subcategory::where('subscriber_id', auth()->id())->findOrFail($id);

        if($subcategory->image && file_exists(public_path('uploads/subcategories/'.$subcategory->image))){
            unlink(public_path('uploads/subcategories/'.$subcategory->image));
        }

        $subcategory->delete();

        return redirect()
            ->route('subscriber.subcategories.index')
            ->with('success', 'Subcategory Deleted Successfully');
    }

    public function quickStore(Request $request)
    {
        $request->validate([
            'category_id' => 'required',
            'name' => 'required',
        ]);

        $exists = Subcategory::whereNull('deleted_at')
            ->where('subscriber_id', auth()->id())
            ->where('category_id', $request->category_id)
            ->where('name', $request->name)
            ->first();
            
        if ($exists) {
            return response()->json([
                'success' => true,
                'subcategory' => $exists,
            ]);
        }

        $subcategory = Subcategory::create([
            'category_id'   => $request->category_id,
            'name'          => $request->name,
            'slug'          => Str::slug($request->name),
            'status'        => 1,
            'subscriber_id' => auth()->id(),
        ]);

        return response()->json([
            'success'     => true,
            'subcategory' => $subcategory,
        ]);
    }
}
