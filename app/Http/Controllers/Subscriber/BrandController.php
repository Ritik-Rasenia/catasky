<?php

namespace App\Http\Controllers\Subscriber;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $brands = Brand::where('subscriber_id', auth()->id())->latest()->get();

        return view('subscriber-panel.brands.index', compact('brands'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('subscriber-panel.brands.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                Rule::unique('brands', 'name')->where(function ($query) {
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
            $image->move(public_path('uploads/brands'), $imageName);
        }

        Brand::create([
            'name'          => $request->name,
            'slug'          => Str::slug($request->name),
            'image'         => $imageName,
            'status'        => $request->status,
            'subscriber_id' => auth()->id(),
        ]);

        return redirect()
            ->route('subscriber.brands.index')
            ->with('success', 'Brand Created Successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $brand = Brand::where('subscriber_id', auth()->id())->findOrFail($id);

        return view('subscriber-panel.brands.show', compact('brand'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $brand = Brand::where('subscriber_id', auth()->id())->findOrFail($id);

        return view('subscriber-panel.brands.edit', compact('brand'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $brand = Brand::where('subscriber_id', auth()->id())->findOrFail($id);

        $request->validate([
            'name' => [
                'required',
                Rule::unique('brands', 'name')
                    ->ignore($brand->id)
                    ->where(function ($query) {
                        return $query->where('subscriber_id', auth()->id())->whereNull('deleted_at');
                    })
            ],
            'image'  => 'nullable|image|mimes:jpg,jpeg,png,webp',
            'status' => 'required',
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
            ->route('subscriber.brands.index')
            ->with('success', 'Brand Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $brand = Brand::where('subscriber_id', auth()->id())->findOrFail($id);

        if($brand->image && file_exists(public_path('uploads/brands/'.$brand->image))){
            unlink(public_path('uploads/brands/'.$brand->image));
        }

        $brand->delete();
 
        return redirect()
            ->route('subscriber.brands.index')
            ->with('success', 'Brand Deleted Successfully');
    }
 
    public function quickStore( \Illuminate\Http\Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                \Illuminate\Validation\Rule::unique('brands', 'name')->where(function ($query) {
                    return $query->where('subscriber_id', auth()->id())->whereNull('deleted_at');
                })
            ],
        ]);
 
        $brand = Brand::create([
            'name'          => $request->name,
            'slug'          => \Illuminate\Support\Str::slug($request->name),
            'status'        => 1,
            'subscriber_id' => auth()->id(),
        ]);
 
        return response()->json([
            'success' => true,
            'brand'   => $brand,
        ]);
    }
}
