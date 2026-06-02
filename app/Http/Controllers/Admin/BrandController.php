<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        abort_if(!auth()->user()->can('view-brands'), 403, 'Unauthorized.');
        $brands = Brand::with('subscriber.subscriberProfile')->latest()->get();

        return view('admin.brands.index', compact('brands'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_if(!auth()->user()->can('create-brands'), 403, 'Unauthorized.');
        return view('admin.brands.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_if(!auth()->user()->can('create-brands'), 403, 'Unauthorized.');
        $request->validate([
            'name'      => [
                'required',
                \Illuminate\Validation\Rule::unique('brands', 'name')->whereNull('subscriber_id')->whereNull('deleted_at')
            ],
            'image'     => 'nullable|image|mimes:jpg,jpeg,png,webp',
            'status'    => 'required',
        ]);


        $imageName = null;

        if($request->hasFile('image')){

            $image = $request->file('image');

            $imageName = time().'.'.$image->getClientOriginalExtension();

            $image->move(public_path('uploads/brands'), $imageName);
        }


        Brand::create([
            'name'      => $request->name,
            'slug'      => Str::slug($request->name),
            'image'     => $imageName,
            'status'    => $request->status,
        ]);


        return redirect()
            ->route('admin.brands.index')
            ->with('success', 'Brand Created Successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        abort_if(!auth()->user()->can('view-brands'), 403, 'Unauthorized.');
        $brand = Brand::findOrFail($id);

        return view('admin.brands.show', compact('brand'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        abort_if(!auth()->user()->can('edit-brands'), 403, 'Unauthorized.');
        $brand = Brand::findOrFail($id);

        return view('admin.brands.edit', compact('brand'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        abort_if(!auth()->user()->can('edit-brands'), 403, 'Unauthorized.');
        $brand = Brand::findOrFail($id);

        $request->validate([
            'name'      => [
                'required',
                \Illuminate\Validation\Rule::unique('brands', 'name')
                    ->ignore($brand->id)
                    ->whereNull('subscriber_id')
                    ->whereNull('deleted_at')
            ],
            'image'     => 'nullable|image|mimes:jpg,jpeg,png,webp',
            'status'    => 'required',
        ]);


        $imageName = $brand->image;

        if($request->hasFile('image')){

            if($brand->image && file_exists(public_path('uploads/brands/'.$brand->image))){

                unlink(public_path('uploads/brands/'.$brand->image));
            }

            $image = $request->file('image');

            $imageName = time().'.'.$image->getClientOriginalExtension();

            $image->move(public_path('uploads/brands'), $imageName);
        }


        $brand->update([
            'name'      => $request->name,
            'slug'      => Str::slug($request->name),
            'image'     => $imageName,
            'status'    => $request->status,
        ]);


        return redirect()
            ->route('admin.brands.index')
            ->with('success', 'Brand Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        abort_if(!auth()->user()->can('delete-brands'), 403, 'Unauthorized.');
        $brand = Brand::findOrFail($id);

        if($brand->image && file_exists(public_path('uploads/brands/'.$brand->image))){

            unlink(public_path('uploads/brands/'.$brand->image));
        }

        $brand->delete();
 
        return redirect()
            ->route('admin.brands.index')
            ->with('success', 'Brand Deleted Successfully');
    }
 
    public function quickStore(Request $request)
    {
        abort_if(!auth()->user()->can('create-brands'), 403, 'Unauthorized.');
        $request->validate([
            'name' => [
                'required',
                \Illuminate\Validation\Rule::unique('brands', 'name')->whereNull('subscriber_id')->whereNull('deleted_at')
            ],
        ]);
 
        $brand = Brand::create([
            'name'   => $request->name,
            'slug'   => Str::slug($request->name),
            'status' => 1,
        ]);
 
        return response()->json([
            'success' => true,
            'brand'   => $brand,
        ]);
    }
}