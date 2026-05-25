<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\AttributeOption;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttributeController extends Controller
{
    /**
     * Display a listing of global attributes and groups.
     */
    public function index(Request $request)
    {
        $query = Attribute::where('is_global', true)->with(['group', 'options']);

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->group_id) {
            $query->where('attribute_group_id', $request->group_id);
        }

        $attributes = $query->orderBy('sort_order')->paginate(20)->withQueryString();
        $groups = AttributeGroup::orderBy('sort_order')->get();

        return view('admin.attributes.index', compact('attributes', 'groups'));
    }

    /**
     * Store a newly created global attribute.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'               => 'required|string|max:255',
            'type'               => 'required|in:text,number,select,multiselect,checkbox,radio,textarea,image,file,color,date,url',
            'attribute_group_id' => 'nullable|exists:attribute_groups,id',
            'unit'               => 'nullable|string|max:50',
            'placeholder'        => 'nullable|string|max:255',
            'default_value'      => 'nullable|string',
            'options'            => 'nullable|string', // Comma-separated options for select type
        ]);

        $slug = Str::slug($request->name);
        $count = Attribute::where('slug', $slug)->count();
        if ($count > 0) {
            $slug .= '-' . Str::random(4);
        }

        $attribute = Attribute::create([
            'user_id'            => auth()->id(), // Admin
            'attribute_group_id' => $request->attribute_group_id,
            'name'               => $request->name,
            'slug'               => $slug,
            'type'               => $request->type,
            'default_value'      => $request->default_value,
            'unit'               => $request->unit,
            'placeholder'        => $request->placeholder,
            'is_required'        => $request->boolean('is_required'),
            'is_searchable'      => $request->boolean('is_searchable'),
            'is_filterable'      => $request->boolean('is_filterable'),
            'is_comparable'      => $request->boolean('is_comparable'),
            'is_variant_enabled' => $request->boolean('is_variant_enabled'),
            'is_global'          => true,
            'approval_status'    => 'approved',
            'is_active'          => true,
            'sort_order'         => Attribute::where('is_global', true)->max('sort_order') + 1,
        ]);

        // Save options if select type
        if ($attribute->isSelectType() && $request->options) {
            $options = array_map('trim', explode(',', $request->options));
            foreach ($options as $index => $optText) {
                if ($optText !== '') {
                    AttributeOption::create([
                        'attribute_id' => $attribute->id,
                        'value'        => Str::slug($optText),
                        'label'        => $optText,
                        'sort_order'   => $index,
                    ]);
                }
            }
        }

        return redirect()->route('admin.attributes.index')
            ->with('success', 'Global attribute created successfully!');
    }

    /**
     * Update an attribute.
     */
    public function update(Request $request, Attribute $attribute)
    {
        $request->validate([
            'name'               => 'required|string|max:255',
            'attribute_group_id' => 'nullable|exists:attribute_groups,id',
            'unit'               => 'nullable|string|max:50',
            'placeholder'        => 'nullable|string|max:255',
            'default_value'      => 'nullable|string',
            'options'            => 'nullable|string',
        ]);

        $attribute->update([
            'attribute_group_id' => $request->attribute_group_id,
            'name'               => $request->name,
            'default_value'      => $request->default_value,
            'unit'               => $request->unit,
            'placeholder'        => $request->placeholder,
            'is_required'        => $request->boolean('is_required'),
            'is_searchable'      => $request->boolean('is_searchable'),
            'is_filterable'      => $request->boolean('is_filterable'),
            'is_comparable'      => $request->boolean('is_comparable'),
            'is_variant_enabled' => $request->boolean('is_variant_enabled'),
            'is_active'          => $request->boolean('is_active', true),
        ]);

        // Recreate options if select type and options passed
        if ($attribute->isSelectType() && $request->options) {
            $attribute->options()->delete();
            $options = array_map('trim', explode(',', $request->options));
            foreach ($options as $index => $optText) {
                if ($optText !== '') {
                    AttributeOption::create([
                        'attribute_id' => $attribute->id,
                        'value'        => Str::slug($optText),
                        'label'        => $optText,
                        'sort_order'   => $index,
                    ]);
                }
            }
        }

        return redirect()->route('admin.attributes.index')
            ->with('success', 'Global attribute updated successfully!');
    }

    /**
     * Delete an attribute.
     */
    public function destroy(Attribute $attribute)
    {
        $attribute->options()->delete();
        $attribute->delete();

        return redirect()->route('admin.attributes.index')
            ->with('success', 'Global attribute deleted.');
    }

    /**
     * Show subscriber custom field approvals dashboard.
     */
    public function approvals()
    {
        $pendingAttributes = Attribute::where('is_global', false)
            ->where('approval_status', 'pending')
            ->with(['subscriber', 'group'])
            ->latest()
            ->paginate(15);

        return view('admin.attributes.approvals', compact('pendingAttributes'));
    }

    /**
     * Return attributes assigned to a given subcategory (AJAX JSON response).
     * Used by product create/edit pages to dynamically render attribute fields.
     */
    public function forSubcategory($subcategoryId)
    {
        $attributes = Attribute::where('is_global', true)
            ->whereJsonContains('subcategories', (int) $subcategoryId)
            ->with(['options', 'group'])
            ->orderBy('sort_order')
            ->get();

        // Normalize for frontend consumption
        $payload = $attributes->map(function ($a) {
            return [
                'id' => $a->id,
                'name' => $a->name,
                'slug' => $a->slug,
                'type' => $a->type,
                'placeholder' => $a->placeholder,
                'unit' => $a->unit,
                'is_required' => (bool) $a->is_required,
                'is_searchable' => (bool) $a->is_searchable,
                'is_filterable' => (bool) $a->is_filterable,
                'is_comparable' => (bool) $a->is_comparable,
                'is_variant_enabled' => (bool) $a->is_variant_enabled,
                'options' => $a->options->map(function($o){
                    return ['id' => $o->id, 'label' => $o->label, 'value' => $o->value, 'color_code' => $o->color_code];
                })->toArray(),
                'group' => $a->group?->name ?? null,
            ];
        });

        return response()->json($payload);
    }

    /**
     * Approve subscriber custom attribute.
     */
    public function approve(Request $request, Attribute $attribute)
    {
        $attribute->update([
            'is_global'       => true, // Promote to global
            'approval_status' => 'approved',
        ]);

        return back()->with('success', "Attribute '{$attribute->name}' approved and promoted to global.");
    }

    /**
     * Reject subscriber custom attribute.
     */
    public function reject(Request $request, Attribute $attribute)
    {
        $attribute->update([
            'approval_status' => 'rejected',
            'is_active'       => false,
        ]);

        return back()->with('success', "Attribute '{$attribute->name}' rejected.");
    }

    /**
     * Attribute Groups management.
     */
    public function storeGroup(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        AttributeGroup::create([
            'name'       => $request->name,
            'slug'       => Str::slug($request->name),
            'sort_order' => AttributeGroup::max('sort_order') + 1,
        ]);

        return back()->with('success', 'Attribute Group created.');
    }
}
