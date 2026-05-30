<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subcategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'image',
        'status',
        'subscriber_id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subscriber()
    {
        return $this->belongsTo(User::class, 'subscriber_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'subcategory_attributes')
                    ->withPivot(['attribute_group_id', 'is_required', 'sort_order'])
                    ->withTimestamps();
    }

    public function subcategoryAttributes()
    {
        return $this->hasMany(SubcategoryAttribute::class);
    }
}
