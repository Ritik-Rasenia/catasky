<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\CategoryAttribute;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    /**
     * Display a listing of category templates.
     */
    public function index()
    {
        $categories = Category::withCount('products')->get();
        
        // Count mapped attributes per category
        $categoryAttributeCounts = CategoryAttribute::groupBy('category_id')
            ->selectRaw('category_id, count(*) as count')
            ->pluck('count', 'category_id');

        return view('admin.templates.index', compact('categories', 'categoryAttributeCounts'));
    }

    /**
     * Show category template edit form.
     */
    public function edit(Category $template)
    {
        // Load all global attributes grouped by their attribute groups
        $attributes = Attribute::where('is_global', true)
            ->with('group')
            ->orderBy('sort_order')
            ->get();

        // Get currently assigned attributes
        $assignedAttributes = CategoryAttribute::where('category_id', $template->id)
            ->get()
            ->keyBy('attribute_id');

        return view('admin.templates.edit', compact('template', 'attributes', 'assignedAttributes'))->with('category', $template);
    }

    /**
     * Update category template attributes.
     */
    public function update(Request $request, Category $template)
    {
        $selectedAttributes = $request->input('attributes', []); // attribute_id => [checked, is_required, sort_order]

        // Clear existing mappings
        CategoryAttribute::where('category_id', $template->id)->delete();

        foreach ($selectedAttributes as $attributeId => $data) {
            if (isset($data['checked'])) {
                $attribute = Attribute::findOrFail($attributeId);
                CategoryAttribute::create([
                    'category_id'        => $template->id,
                    'attribute_id'       => $attributeId,
                    'attribute_group_id' => $attribute->attribute_group_id,
                    'is_required'        => isset($data['is_required']),
                    'sort_order'         => intval($data['sort_order'] ?? 0),
                ]);
            }
        }

        return redirect()->route('admin.templates.index')
            ->with('success', "Template for '{$template->name}' updated successfully!");
    }
}
