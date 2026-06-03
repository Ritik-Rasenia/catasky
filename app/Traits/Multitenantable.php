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
                // If the authenticated user is a Subscriber or Admin, save their ID
                if (auth()->user()->hasAnyRole(['Subscriber', 'Admin'])) {
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
            $isDashboard = request()->is('dashboard*') || request()->is('api/notifications*');

            if ($isDashboard && auth()->check()) {
                // For authenticated users in dashboard: if they are a Subscriber or Admin, filter by their user ID
                if (auth()->user()->hasAnyRole(['Subscriber', 'Admin'])) {
                    $builder->where($builder->getQuery()->from . '.subscriber_id', auth()->id());
                }
            } else {
                // Resolve active subscriber context if any (from custom domain or route slug)
                $subscriberId = request()->attributes->get('custom_domain_subscriber_id');
                if (!$subscriberId && request()->route('company_slug')) {
                    $slug = request()->route('company_slug');
                    // Cache lookup in request attributes to avoid duplicate queries
                    if (!request()->attributes->has('resolved_company_subscriber_id')) {
                        $subId = \App\Models\SubscriberProfile::where('company_slug', $slug)->value('user_id');
                        request()->attributes->set('resolved_company_subscriber_id', $subId);
                    }
                    $subscriberId = request()->attributes->get('resolved_company_subscriber_id');
                }

                if ($subscriberId) {
                    // For a specific subscriber storefront: show only their own data or global shared data
                    $builder->where(function($q) use ($builder, $subscriberId) {
                        $q->where($builder->getQuery()->from . '.subscriber_id', $subscriberId)
                          ->orWhereNull($builder->getQuery()->from . '.subscriber_id');
                    });
                } else {
                    // For guests and frontend views: default to showing only global main-site data (subscriber_id IS NULL).
                    // Subscriber store views will explicitly call withoutGlobalScope('tenant').
                    // If a demo user exists, we also allow querying their products on the public storefront.
                    $demoUser = \App\Models\User::where('email', 'like', '%demo%')
                        ->orWhere('name', 'like', '%demo%')
                        ->first();

                    if ($demoUser) {
                        $builder->where(function($q) use ($builder, $demoUser) {
                            $q->whereNull($builder->getQuery()->from . '.subscriber_id')
                              ->orWhere($builder->getQuery()->from . '.subscriber_id', $demoUser->id);
                        });
                    } else {
                        $builder->whereNull($builder->getQuery()->from . '.subscriber_id');
                    }
                }
            }
        });
    }
}
