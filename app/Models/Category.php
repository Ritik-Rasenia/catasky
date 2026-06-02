<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Multitenantable;

class Category extends Model
{
    use HasFactory, SoftDeletes, Multitenantable;

    protected $fillable = [
        'name',
        'slug',
        'image',
        'status',
        'subscriber_id',
    ];

    public function subcategories()
    {
        return $this->hasMany(Subcategory::class);
    }

    public function subscriber()
    {
        return $this->belongsTo(User::class, 'subscriber_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
