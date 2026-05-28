<?php

namespace App\Http\Controllers\Subscriber;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\AttributeOption;
use App\Models\SubscriberActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttributeController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Attribute::where('user_id', $user->id)->with(['group', 'options']);

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->group_id) {
            $query->where('attribute_group_id', $request->group_id);
        }

        $attributes = $query->orderBy('sort_order')->paginate(20);
        $groups = AttributeGroup::where('user_id', $user->id)->orderBy('name')->get();

        return view('subscriber-panel.attributes.index', compact('attributes', 'groups'));
    }

    public function create()
    {
        $user = auth()->user();
        $groups = AttributeGroup::where('user_id', $user->id)->orderBy('sort_order')->get();
        $types = Attribute::TYPES;
        $categories = \App\Models\Category::orderBy('name')->get();
        return view('subscriber-panel.attributes.create', compact('groups', 'types', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'               => 'required|string|max:255',
            'type'               => 'required|in:' . implode(',', array_keys(Attribute::TYPES)),
            'category_id'        => 'required|exists:categories,id',
            'attribute_group_id' => 'nullable|exists:attribute_groups,id',
            'options.*.label'    => 'required_if:type,select,multiselect,checkbox,radio',
        ]);

        $user = auth()->user();

        $attribute = Attribute::create([
            'user_id'            => $user->id,
            'attribute_group_id' => $request->attribute_group_id,
            'name'               => $request->name,
            'slug'               => Str::slug($request->name) . '-' . Str::random(4),
            'type'               => $request->type,
            'default_value'      => $request->default_value,
            'unit'               => $request->unit,
            'placeholder'        => $request->placeholder,
            'is_required'        => $request->boolean('is_required'),
            'is_searchable'      => $request->boolean('is_searchable'),
            'show_in_pdf'        => $request->boolean('show_in_pdf', true),
            'show_in_share'      => $request->boolean('show_in_share', true),
            'is_active'          => $request->boolean('is_active', true),
            'is_global'          => false,
            'approval_status'    => 'pending', // Awaiting Super Admin check
            'sort_order'         => $request->sort_order ?? 0,
            'validation_rules'   => $request->validation_rules ? json_decode($request->validation_rules, true) : null,
        ]);

        // Map attribute to target Category
        \App\Models\CategoryAttribute::create([
            'category_id'        => $request->category_id,
            'attribute_id'       => $attribute->id,
            'attribute_group_id' => $request->attribute_group_id,
            'is_required'        => $request->boolean('is_required'),
            'sort_order'         => 0,
        ]);

        // Save options for select-type attributes
        if ($request->options && in_array($request->type, ['select', 'multiselect', 'checkbox', 'radio'])) {
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
        }

        SubscriberActivityLog::log('created', 'Created attribute: ' . $attribute->name, $attribute);

        // Notify Super Admins of new custom attribute request
        try {
            $superAdmins = \App\Models\User::role('Super Admin')->get();
            if ($superAdmins->isNotEmpty()) {
                \Illuminate\Support\Facades\Notification::send($superAdmins, new \App\Notifications\AttributeRequestNotification([
                    'title' => 'New Attribute Request',
                    'message' => 'Subscriber ' . $user->name . ' has requested approval for a new custom attribute: "' . $attribute->name . '".',
                    'action_url' => url('/dashboard/saas/approvals'),
                ]));
            }
        } catch (\Exception $e) {}

        return redirect()->route('subscriber.attributes.index')
            ->with('success', 'Attribute created successfully!');
    }

    public function edit(Attribute $attribute)
    {
        $this->authorize($attribute);
        $user = auth()->user();
        $groups = AttributeGroup::where('user_id', $user->id)->orderBy('sort_order')->get();
        $types = Attribute::TYPES;
        $attribute->load('options', 'group');
        return view('subscriber-panel.attributes.edit', compact('attribute', 'groups', 'types'));
    }

    public function update(Request $request, Attribute $attribute)
    {
        $this->authorize($attribute);
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', array_keys(Attribute::TYPES)),
        ]);

        $attribute->update([
            'attribute_group_id' => $request->attribute_group_id,
            'name'               => $request->name,
            'type'               => $request->type,
            'default_value'      => $request->default_value,
            'unit'               => $request->unit,
            'placeholder'        => $request->placeholder,
            'is_required'        => $request->boolean('is_required'),
            'is_searchable'      => $request->boolean('is_searchable'),
            'show_in_pdf'        => $request->boolean('show_in_pdf', true),
            'show_in_share'      => $request->boolean('show_in_share', true),
            'is_active'          => $request->boolean('is_active', true),
            'sort_order'         => $request->sort_order ?? 0,
        ]);

        // Sync options
        if ($attribute->isSelectType()) {
            $attribute->options()->delete();
            if ($request->options) {
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
            }
        }

        return redirect()->route('subscriber.attributes.index')
            ->with('success', 'Attribute updated successfully!');
    }

    public function destroy(Attribute $attribute)
    {
        $this->authorize($attribute);
        $attribute->delete();
        return back()->with('success', 'Attribute deleted.');
    }

    public function reorder(Request $request)
    {
        $request->validate(['order' => 'required|array']);
        $user = auth()->user();

        foreach ($request->order as $item) {
            Attribute::where('id', $item['id'])
                ->where('user_id', $user->id)
                ->update(['sort_order' => $item['order']]);
        }

        return response()->json(['success' => true]);
    }

    public function storeCustom(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'type'            => 'required|string',
            'group_section'   => 'required|in:Basic Details,Technical Specifications,Packaging Details,Compliance & Safety,Commercial Details',
            'subcategory_id'  => 'required|exists:subcategories,id',
            'options'         => 'nullable|string',
        ]);

        $user = auth()->user();

        // Find or create group
        $group = AttributeGroup::firstOrCreate(
            ['user_id' => $user->id, 'name' => $request->group_section],
            ['slug' => Str::slug($request->group_section), 'is_active' => true, 'sort_order' => 0]
        );

        // Map UI type to database type
        $uiType = $request->type;
        $dbType = $uiType;
        if ($uiType === 'rich_text') {
            $dbType = 'textarea';
        } elseif ($uiType === 'decimal') {
            $dbType = 'number';
        }

        $attribute = Attribute::create([
            'user_id'            => $user->id,
            'attribute_group_id' => $group->id,
            'name'               => $request->name,
            'slug'               => Str::slug($request->name) . '-' . Str::random(4),
            'type'               => $dbType,
            'is_required'        => false,
            'is_searchable'      => false,
            'show_in_pdf'        => true,
            'show_in_share'      => true,
            'is_active'          => true,
            'is_global'          => false,
            'approval_status'    => 'pending',
            'sort_order'         => 0,
        ]);

        // If there are options for select/multiselect
        if (in_array($dbType, ['select', 'multiselect', 'checkbox', 'radio']) && $request->options) {
            $optionsArr = array_map('trim', explode(',', $request->options));
            foreach ($optionsArr as $index => $optionVal) {
                if (!empty($optionVal)) {
                    AttributeOption::create([
                        'attribute_id' => $attribute->id,
                        'label'        => $optionVal,
                        'value'        => Str::slug($optionVal),
                        'sort_order'   => $index,
                        'is_default'   => false,
                    ]);
                }
            }
        }

        // Map to subcategory
        \App\Models\SubcategoryAttribute::create([
            'subcategory_id'     => $request->subcategory_id,
            'attribute_id'       => $attribute->id,
            'attribute_group_id' => $group->id,
            'is_required'        => false,
            'sort_order'         => 0,
        ]);

        // Activity Log
        SubscriberActivityLog::log('created', 'Requested custom specification attribute: ' . $attribute->name, $attribute);

        // Notify Super Admins
        try {
            $superAdmins = \App\Models\User::role('Super Admin')->get();
            if ($superAdmins->isNotEmpty()) {
                \Illuminate\Support\Facades\Notification::send($superAdmins, new \App\Notifications\AttributeRequestNotification([
                    'title' => 'New Attribute Request',
                    'message' => 'Subscriber ' . $user->name . ' has requested approval for a new B2B custom attribute: "' . $attribute->name . '".',
                    'icon' => 'bi-sliders',
                    'action_url' => url('/dashboard/saas/approvals'),
                ]));
            }
        } catch (\Exception $e) {}

        // Fetch options if select
        $options = $attribute->options->map(function($opt) {
            return [
                'value' => $opt->value,
                'label' => $opt->label
            ];
        });

        return response()->json([
            'success'         => true,
            'attribute'       => [
                'id'              => $attribute->id,
                'name'            => $attribute->name,
                'type'            => $uiType,
                'unit'            => $attribute->unit,
                'placeholder'     => $attribute->placeholder,
                'default_value'   => $attribute->default_value,
                'is_required'     => false,
                'approval_status' => 'pending',
                'options'         => $options
            ],
            'group_name'      => $request->group_section
        ]);
    }

    private function authorize(Attribute $attribute): void
    {
        if ($attribute->user_id !== auth()->id()) {
            abort(403);
        }
    }
}
