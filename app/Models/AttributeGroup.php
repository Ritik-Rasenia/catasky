<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttributeGroup extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'name', 'slug', 'description', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function subscriber()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function attributes()
    {
        return $this->hasMany(Attribute::class)->orderBy('sort_order');
    }
}
