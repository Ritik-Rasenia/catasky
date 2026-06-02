<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Multitenantable;

class Attribute extends Model
{
    use SoftDeletes, Multitenantable;

    protected $fillable = [
        'user_id', 'attribute_group_id', 'name', 'slug', 'type',
        'default_value', 'validation_rules', 'unit', 'placeholder',
        'is_required', 'is_searchable', 'is_filterable', 'is_comparable',
        'is_variant_enabled', 'is_global', 'approval_status',
        'show_in_pdf', 'show_in_share', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'validation_rules' => 'array',
        'is_required' => 'boolean',
        'is_searchable' => 'boolean',
        'is_filterable' => 'boolean',
        'is_comparable' => 'boolean',
        'is_variant_enabled' => 'boolean',
        'is_global' => 'boolean',
        'show_in_pdf' => 'boolean',
        'show_in_share' => 'boolean',
        'is_active' => 'boolean',
    ];

    public const TYPES = [
        'text' => 'Text',
        'number' => 'Number',
        'select' => 'Select (Single)',
        'multiselect' => 'Multi Select',
        'boolean' => 'Boolean',
        'checkbox' => 'Checkbox',
        'radio' => 'Radio',
        'textarea' => 'Textarea',
        'image' => 'Image Upload',
        'file' => 'File Upload',
        'color' => 'Color Picker',
        'date' => 'Date',
        'url' => 'URL',
    ];

    public function subscriber()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function group()
    {
        return $this->belongsTo(AttributeGroup::class, 'attribute_group_id');
    }

    public function options()
    {
        return $this->hasMany(AttributeOption::class)->orderBy('sort_order');
    }

    public function productValues()
    {
        return $this->hasMany(SubscriberProductAttributeValue::class);
    }

    public function isSelectType(): bool
    {
        return in_array($this->type, ['select', 'multiselect', 'checkbox', 'radio']);
    }
}
