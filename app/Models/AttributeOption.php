<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeOption extends Model
{
    protected $fillable = [
        'attribute_id', 'label', 'value', 'color_code', 'sort_order', 'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }
}
