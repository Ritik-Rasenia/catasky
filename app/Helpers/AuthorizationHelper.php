<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class AuthorizationHelper
{
    /**
     * Check if user has permission
     */
    public static function hasPermission($permission)
    {
        if (!Auth::check()) {
            return false;
        }

        return Auth::user()->can($permission) || Auth::user()->hasRole('Super Admin');
    }

    /**
     * Check if user has any permission
     */
    public static function hasAnyPermission($permissions)
    {
        if (!Auth::check()) {
            return false;
        }

        if (Auth::user()->hasRole('Super Admin')) {
            return true;
        }

        $permissions = is_array($permissions) ? $permissions : func_get_args();
        
        return Auth::user()->hasAnyPermission($permissions);
    }

    /**
     * Check if user has all permissions
     */
    public static function hasAllPermissions($permissions)
    {
        if (!Auth::check()) {
            return false;
        }

        if (Auth::user()->hasRole('Super Admin')) {
            return true;
        }

        $permissions = is_array($permissions) ? $permissions : func_get_args();
        
        return Auth::user()->hasAllPermissions($permissions);
    }

    /**
     * Check if user has role
     */
    public static function hasRole($role)
    {
        if (!Auth::check()) {
            return false;
        }

        return Auth::user()->hasRole($role);
    }

    /**
     * Check if user has any role
     */
    public static function hasAnyRole($roles)
    {
        if (!Auth::check()) {
            return false;
        }

        $roles = is_array($roles) ? $roles : func_get_args();
        
        return Auth::user()->hasAnyRole($roles);
    }

    /**
     * Check if user is admin (super admin or admin role)
     */
    public static function isAdmin()
    {
        if (!Auth::check()) {
            return false;
        }

        return Auth::user()->hasAnyRole(['Super Admin', 'Admin']);
    }

    /**
     * Check if user is super admin
     */
    public static function isSuperAdmin()
    {
        if (!Auth::check()) {
            return false;
        }

        return Auth::user()->hasRole('Super Admin');
    }

    /**
     * Get user roles
     */
    public static function getUserRoles()
    {
        if (!Auth::check()) {
            return collect();
        }

        return Auth::user()->roles;
    }

    /**
     * Get user permissions
     */
    public static function getUserPermissions()
    {
        if (!Auth::check()) {
            return collect();
        }

        return Auth::user()->getAllPermissions();
    }
}
