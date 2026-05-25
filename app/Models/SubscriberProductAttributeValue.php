<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriberProductAttributeValue extends Model
{
    protected $table = 'subscriber_product_attribute_values';

    protected $fillable = [
        'subscriber_product_id', 'attribute_id', 'value',
    ];

    public function product()
    {
        return $this->belongsTo(SubscriberProduct::class, 'subscriber_product_id');
    }

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    public function getDisplayValueAttribute(): string
    {
        $attr = $this->attribute;
        if (!$attr) return $this->value ?? '';

        if (in_array($attr->type, ['multiselect', 'checkbox'])) {
            $values = json_decode($this->value, true);
            if (is_array($values)) {
                return implode(', ', $values);
            }
        }

        if ($attr->type === 'color' && $this->value) {
            return '<span style="display:inline-block;width:16px;height:16px;background:' . $this->value . ';border-radius:3px;margin-right:4px;"></span>' . $this->value;
        }

        return $this->value ?? '';
    }
}
