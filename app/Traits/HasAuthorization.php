<?php

namespace App\Traits;

trait HasAuthorization
{
    /**
     * Check if the user can access a feature/module
     */
    public function canAccess($permission)
    {
        if (!auth()->check()) {
            return false;
        }

        return auth()->user()->can($permission) || auth()->user()->hasRole('admin');
    }

    /**
     * Check if the user has a specific role
     */
    public function hasRole($role)
    {
        if (!auth()->check()) {
            return false;
        }

        return auth()->user()->hasRole($role);
    }

    /**
     * Check if the user has any role from a list
     */
    public function hasAnyRole(...$roles)
    {
        if (!auth()->check()) {
            return false;
        }

        return auth()->user()->hasAnyRole($roles);
    }

    /**
     * Check if the user has all roles from a list
     */
    public function hasAllRoles(...$roles)
    {
        if (!auth()->check()) {
            return false;
        }

        return auth()->user()->hasAllRoles($roles);
    }

    /**
     * Check if the user has a specific permission
     */
    public function hasPermission($permission)
    {
        if (!auth()->check()) {
            return false;
        }

        return auth()->user()->hasPermissionTo($permission) || auth()->user()->hasRole('admin');
    }

    /**
     * Check if the user has any permission from a list
     */
    public function hasAnyPermission(...$permissions)
    {
        if (!auth()->check()) {
            return false;
        }

        return auth()->user()->hasAnyPermission($permissions) || auth()->user()->hasRole('admin');
    }

    /**
     * Check if the user has all permissions from a list
     */
    public function hasAllPermissions(...$permissions)
    {
        if (!auth()->check()) {
            return false;
        }

        return auth()->user()->hasAllPermissions($permissions) || auth()->user()->hasRole('admin');
    }
}
