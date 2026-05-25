<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubcategoryAttribute extends Model
{
    protected $fillable = [
        'subcategory_id', 'attribute_id', 'attribute_group_id',
        'is_required', 'sort_order'
    ];

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
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
