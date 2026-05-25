<?php

namespace App\Http\Controllers\Subscriber;

use App\Http\Controllers\Controller;
use App\Models\AttributeGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttributeGroupController extends Controller
{
    public function index()
    {
        $groups = AttributeGroup::where('user_id', auth()->id())
            ->withCount('attributes')
            ->orderBy('sort_order')
            ->get();
        return view('subscriber-panel.attribute-groups.index', compact('groups'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        AttributeGroup::create([
            'user_id'    => auth()->id(),
            'name'       => $request->name,
            'slug'       => Str::slug($request->name) . '-' . Str::random(4),
            'description'=> $request->description,
            'sort_order' => AttributeGroup::where('user_id', auth()->id())->max('sort_order') + 1,
        ]);
        return back()->with('success', 'Attribute group created!');
    }

    public function update(Request $request, AttributeGroup $attributeGroup)
    {
        if ($attributeGroup->user_id !== auth()->id()) abort(403);
        $request->validate(['name' => 'required|string|max:255']);
        $attributeGroup->update([
            'name'        => $request->name,
            'description' => $request->description,
            'is_active'   => $request->boolean('is_active', true),
        ]);
        return back()->with('success', 'Group updated!');
    }

    public function destroy(AttributeGroup $attributeGroup)
    {
        if ($attributeGroup->user_id !== auth()->id()) abort(403);
        $attributeGroup->delete();
        return back()->with('success', 'Group deleted.');
    }

    public function reorder(Request $request)
    {
        $request->validate(['order' => 'required|array']);
        foreach ($request->order as $item) {
            AttributeGroup::where('id', $item['id'])
                ->where('user_id', auth()->id())
                ->update(['sort_order' => $item['order']]);
        }
        return response()->json(['success' => true]);
    }
}
