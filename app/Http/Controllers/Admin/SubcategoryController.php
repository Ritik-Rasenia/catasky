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
        $subcategories = Subcategory::with(['category', 'subscriber.subscriberProfile'])->latest()->get();

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
            'name'        => [
                'required',
                \Illuminate\Validation\Rule::unique('subcategories', 'name')
                    ->where(function ($query) {
                        $user = auth()->user();
                        if ($user && $user->isDemo()) {
                            return $query->where('subscriber_id', $user->id);
                        }
                        return $query->whereNull('subscriber_id');
                    })
                    ->whereNull('deleted_at')
            ],
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
        $attributes = \App\Models\Attribute::where('is_global', true)->orderBy('name')->get();
        $selectedAttributeIds = \App\Models\SubcategoryAttribute::where('subcategory_id', $id)->pluck('attribute_id')->toArray();
        return view('admin.subcategories.edit', compact('subcategory', 'categories', 'attributes', 'selectedAttributeIds'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $subcategory = Subcategory::findOrFail($id);

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => [
                'required',
                \Illuminate\Validation\Rule::unique('subcategories', 'name')
                    ->ignore($subcategory->id)
                    ->where(function ($query) {
                        $user = auth()->user();
                        if ($user && $user->isDemo()) {
                            return $query->where('subscriber_id', $user->id);
                        }
                        return $query->whereNull('subscriber_id');
                    })
                    ->whereNull('deleted_at')
            ],
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

        // Sync subcategory attributes
        \App\Models\SubcategoryAttribute::where('subcategory_id', $subcategory->id)->delete();
        if ($request->has('attributes')) {
            foreach ($request->input('attributes') as $attrId) {
                $attr = \App\Models\Attribute::find($attrId);
                if ($attr) {
                    \App\Models\SubcategoryAttribute::create([
                        'subcategory_id' => $subcategory->id,
                        'attribute_id' => $attrId,
                        'attribute_group_id' => $attr->attribute_group_id,
                        'is_required' => $attr->is_required,
                        'sort_order' => 0,
                    ]);
                }
            }
        }

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

    public function quickStore(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required',
        ]);

        $exists = Subcategory::whereNull('deleted_at')
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
            'category_id' => $request->category_id,
            'name'        => $request->name,
            'slug'        => \Illuminate\Support\Str::slug($request->name),
            'status'      => 1,
        ]);

        return response()->json([
            'success'     => true,
            'subcategory' => $subcategory,
        ]);
    }
}
