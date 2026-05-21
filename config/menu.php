<?php

return [
    // Sample admin menu configuration with permission keys.
    // The existing sidebar layout can use this config to render
    // only allowed menu items for the authenticated user.
    'admin' => [
        [
            'label' => 'Dashboard',
            'route' => 'admin.dashboard',
            'icon' => 'heroicon-o-home',
            'permission' => null,
        ],
        [
            'label' => 'Users',
            'route' => 'admin.users.index',
            'icon' => 'heroicon-o-users',
            'permission' => 'users.view',
        ],
        [
            'label' => 'Roles & Permissions',
            'route' => 'admin.roles.index',
            'icon' => 'heroicon-o-shield-check',
            'permission' => 'roles.view',
        ],
        [
            'label' => 'Products',
            'route' => 'admin.products.index',
            'icon' => 'heroicon-o-archive',
            'permission' => 'products.view',
        ],
    ],
];
