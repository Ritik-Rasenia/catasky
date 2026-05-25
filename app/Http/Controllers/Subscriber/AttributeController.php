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
        return view('subscriber-panel.attributes.create', compact('groups', 'types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'               => 'required|string|max:255',
            'type'               => 'required|in:' . implode(',', array_keys(Attribute::TYPES)),
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
            'sort_order'         => $request->sort_order ?? 0,
            'validation_rules'   => $request->validation_rules ? json_decode($request->validation_rules, true) : null,
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

    private function authorize(Attribute $attribute): void
    {
        if ($attribute->user_id !== auth()->id()) {
            abort(403);
        }
    }
}
