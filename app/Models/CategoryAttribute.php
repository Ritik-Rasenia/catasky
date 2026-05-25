<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryAttribute extends Model
{
    protected $fillable = [
        'category_id', 'attribute_id', 'attribute_group_id',
        'is_required', 'sort_order'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    public function attributeGroup()
    {
        return $this->belongsTo(AttributeGroup::class);
    }
}
