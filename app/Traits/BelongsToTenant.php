<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant()
    {
        static::creating(function (Model $model) {
            if (auth()->check() && ! $model->subscriber_id) {
                if (auth()->user()->hasRole('Subscriber')) {
                    $model->subscriber_id = auth()->id();
                }
            }
        });

        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->check()) {
                $user = auth()->user();
                if ($user->hasRole('Subscriber')) {
                    $builder->where('subscriber_id', $user->id);
                }
            }
        });
    }

    public function subscriber()
    {
        return $this->belongsTo(User::class, 'subscriber_id');
    }
}
