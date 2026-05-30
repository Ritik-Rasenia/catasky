<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\AttributeOption;
use App\Models\Subcategory;
use App\Models\SubcategoryAttribute;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttributeController extends Controller
{
    /**
     * Display a listing of global attributes and groups.
     */
    public function index(Request $request)
    {
        $query = Attribute::where('is_global', true)->with(['group', 'options', 'subscriber.subscriberProfile']);

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
     * Show the form for creating a new global attribute.
     */
    public function create()
    {
        $groups = AttributeGroup::orderBy('sort_order')->get();
        $types = Attribute::TYPES;
        $subcategories = Subcategory::with('category')->orderBy('name')->get();
        return view('admin.attributes.create', compact('groups', 'types', 'subcategories'));
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
            'options'            => 'nullable', // supports structured array OR comma-separated string
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

        // Save options if select/list type
        if ($attribute->isSelectType() && $request->options) {
            if (is_array($request->options)) {
                // Structured Options Builder format
                foreach ($request->options as $index => $option) {
                    if (!empty($option['label'])) {
                        AttributeOption::create([
                            'attribute_id' => $attribute->id,
                            'label'        => $option['label'],
                            'value'        => $option['value'] ?? Str::slug($option['label']),
                            'color_code'   => $option['color_code'] ?? null,
                            'sort_order'   => $index,
                            'is_default'   => !empty($option['is_default']),
                        ]);
                    }
                }
            } else {
                // Comma-separated string format
                $options = array_map('trim', explode(',', $request->options));
                foreach ($options as $index => $optText) {
                    if ($optText !== '') {
                        AttributeOption::create([
                            'attribute_id' => $attribute->id,
                            'value'        => Str::slug($optText),
                            'label'        => $optText,
                            'sort_order'   => $index,
                            'is_default'   => false,
                        ]);
                    }
                }
            }
        }

        // Sync Subcategory Mappings
        if ($request->has('subcategories')) {
            foreach ($request->input('subcategories') as $subcatId) {
                SubcategoryAttribute::create([
                    'subcategory_id'     => $subcatId,
                    'attribute_id'       => $attribute->id,
                    'attribute_group_id' => $request->attribute_group_id,
                    'is_required'        => $request->boolean('is_required'),
                    'sort_order'         => 0,
                ]);
            }
        }

        return redirect()->route('admin.attributes.index')
            ->with('success', 'Global attribute created successfully!');
    }

    /**
     * Show the edit form for a global attribute.
     */
    public function edit($id)
    {
        $attribute = Attribute::findOrFail($id);
        $groups = AttributeGroup::orderBy('sort_order')->get();
        $subcategories = Subcategory::with('category')->orderBy('name')->get();
        
        // Fetch currently mapped subcategories
        $selectedSubcategoryIds = SubcategoryAttribute::where('attribute_id', $attribute->id)
            ->pluck('subcategory_id')
            ->toArray();
            
        $types = Attribute::TYPES;
        
        return view('admin.attributes.edit', compact('attribute', 'groups', 'subcategories', 'selectedSubcategoryIds', 'types'));
    }

    /**
     * Update an attribute.
     */
    public function update(Request $request, $attribute)
    {
        if (!$attribute instanceof Attribute) {
            $attribute = Attribute::findOrFail($attribute);
        }

        $request->validate([
            'name'               => 'required|string|max:255',
            'attribute_group_id' => 'nullable|exists:attribute_groups,id',
            'unit'               => 'nullable|string|max:50',
            'placeholder'        => 'nullable|string|max:255',
            'default_value'      => 'nullable|string',
            'options'            => 'nullable',
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

        // Recreate options if select/list type
        if ($attribute->isSelectType()) {
            $attribute->options()->delete();
            if ($request->options) {
                if (is_array($request->options)) {
                    // Structured Options Builder format
                    foreach ($request->options as $index => $option) {
                        if (!empty($option['label'])) {
                            AttributeOption::create([
                                'attribute_id' => $attribute->id,
                                'label'        => $option['label'],
                                'value'        => $option['value'] ?? Str::slug($option['label']),
                                'color_code'   => $option['color_code'] ?? null,
                                'sort_order'   => $index,
                                'is_default'   => !empty($option['is_default']),
                            ]);
                        }
                    }
                } else {
                    // Comma-separated string format
                    $options = array_map('trim', explode(',', $request->options));
                    foreach ($options as $index => $optText) {
                        if ($optText !== '') {
                            AttributeOption::create([
                                'attribute_id' => $attribute->id,
                                'value'        => Str::slug($optText),
                                'label'        => $optText,
                                'sort_order'   => $index,
                                'is_default'   => false,
                            ]);
                        }
                    }
                }
            }
        }

        // Sync Subcategory Mappings
        SubcategoryAttribute::where('attribute_id', $attribute->id)->delete();
        if ($request->has('subcategories')) {
            foreach ($request->input('subcategories') as $subcatId) {
                SubcategoryAttribute::create([
                    'subcategory_id'     => $subcatId,
                    'attribute_id'       => $attribute->id,
                    'attribute_group_id' => $request->attribute_group_id,
                    'is_required'        => $request->boolean('is_required'),
                    'sort_order'         => 0,
                ]);
            }
        }

        return redirect()->route('admin.attributes.index')
            ->with('success', 'Global attribute updated successfully!');
    }

    /**
     * Delete an attribute.
     */
    public function destroy($attribute)
    {
        if (!$attribute instanceof Attribute) {
            $attribute = Attribute::findOrFail($attribute);
        }

        $attribute->options()->delete();
        SubcategoryAttribute::where('attribute_id', $attribute->id)->delete();
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
        $subcatAttrs = SubcategoryAttribute::where('subcategory_id', $subcategoryId)
            ->with(['attribute.options', 'attribute.group'])
            ->get();

        // Group attributes using mapGroupSection
        $grouped = $subcatAttrs->groupBy(function($subcatAttr) {
            return $this->mapGroupSection($subcatAttr->attribute?->group?->name);
        });

        // Ensure we order them strictly: Basic, Technical, Packaging, Compliance, Commercial
        $orderedGroups = [
            'Basic Details',
            'Technical Specifications',
            'Packaging Details',
            'Compliance & Safety',
            'Commercial Details'
        ];

        $result = [];
        foreach ($orderedGroups as $groupName) {
            if (!$grouped->has($groupName)) {
                continue;
            }
            $subcatAttrsInGroup = $grouped[$groupName];
            $mappedAttrs = [];
            foreach ($subcatAttrsInGroup as $subcatAttr) {
                $attr = $subcatAttr->attribute;
                if (!$attr || !$attr->is_active) {
                    continue;
                }
                $mappedAttrs[] = [
                    'id' => $attr->id,
                    'name' => $attr->name,
                    'type' => $attr->type,
                    'unit' => $attr->unit,
                    'placeholder' => $attr->placeholder,
                    'default_value' => $attr->default_value,
                    'is_required' => (bool)$subcatAttr->is_required,
                    'approval_status' => $attr->approval_status,
                    'options' => $attr->options->map(function($opt) {
                        return [
                            'value' => $opt->value,
                            'label' => $opt->label,
                            'is_default' => (bool)$opt->is_default
                        ];
                    })
                ];
            }
            if (!empty($mappedAttrs)) {
                $result[] = [
                    'group_name' => $groupName,
                    'attributes' => $mappedAttrs
                ];
            }
        }

        return response()->json($result);
    }



    /**
     * Map any group name into one of the 5 standard sections strictly.
     */
    private function mapGroupSection(?string $groupName): string
    {
        if (!$groupName) {
            return 'Basic Details';
        }
        
        $name = strtolower(trim($groupName));
        
        if (str_contains($name, 'basic') || str_contains($name, 'general') || str_contains($name, 'overview') || str_contains($name, 'primary')) {
            return 'Basic Details';
        }
        if (str_contains($name, 'tech') || str_contains($name, 'spec') || str_contains($name, 'feature') || str_contains($name, 'detail') || str_contains($name, 'performance')) {
            return 'Technical Specifications';
        }
        if (str_contains($name, 'pack') || str_contains($name, 'box') || str_contains($name, 'shipping') || str_contains($name, 'dimension')) {
            return 'Packaging Details';
        }
        if (str_contains($name, 'compliance') || str_contains($name, 'safety') || str_contains($name, 'cert') || str_contains($name, 'standard') || str_contains($name, 'legal')) {
            return 'Compliance & Safety';
        }
        if (str_contains($name, 'commercial') || str_contains($name, 'price') || str_contains($name, 'cost') || str_contains($name, 'sale') || str_contains($name, 'sell') || str_contains($name, 'trade') || str_contains($name, 'vendor')) {
            return 'Commercial Details';
        }
        
        return 'Technical Specifications';
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

        // Notify subscriber of attribute approval
        try {
            $user = \App\Models\User::find($attribute->user_id);
            if ($user) {
                $user->notify(new \App\Notifications\AttributeRequestNotification([
                    'title' => 'Attribute Approved',
                    'message' => 'Your custom attribute request for "' . $attribute->name . '" has been approved.',
                    'icon' => 'bi-sliders',
                    'action_url' => route('subscriber.attributes.index'),
                ]));
            }
        } catch (\Exception $e) {}

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

        // Notify subscriber of attribute rejection
        try {
            $user = \App\Models\User::find($attribute->user_id);
            if ($user) {
                $user->notify(new \App\Notifications\AttributeRequestNotification([
                    'title' => 'Attribute Rejected',
                    'message' => 'Your custom attribute request for "' . $attribute->name . '" has been rejected.',
                    'icon' => 'bi-x-octagon',
                    'action_url' => route('subscriber.attributes.index'),
                ]));
            }
        } catch (\Exception $e) {}

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
