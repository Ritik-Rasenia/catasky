<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Solution extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'image',
        'icon',
        'short_description',
        'description',
        'status',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }
}
