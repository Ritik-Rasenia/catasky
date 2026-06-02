<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Multitenantable
{
    public static function bootMultitenantable()
    {
        // 1. Automatically assign subscriber_id and generate slug when creating a record
        static::creating(function ($model) {
            if (auth()->check()) {
                // If the authenticated user is NOT a Super Admin, save their ID
                if (!auth()->user()->hasRole('Super Admin')) {
                    $model->subscriber_id = auth()->id();
                }
            }
            // Auto-generate slug if it exists in fillable and is empty
            if (in_array('slug', $model->getFillable()) && empty($model->slug) && !empty($model->name)) {
                $model->slug = \Illuminate\Support\Str::slug($model->name) . '-' . \Illuminate\Support\Str::random(6);
            }
        });

        // 2. Automatically scope queries based on role / guest status
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->check()) {
                // For authenticated users: if NOT a Super Admin, filter by their user ID
                if (!auth()->user()->hasRole('Super Admin')) {
                    $builder->where($builder->getQuery()->from . '.subscriber_id', auth()->id());
                }
            } else {
                // For guests: default to showing only global main-site data (subscriber_id IS NULL).
                // Subscriber store views will explicitly call withoutGlobalScope('tenant').
                $builder->whereNull($builder->getQuery()->from . '.subscriber_id');
            }
        });
    }
}
