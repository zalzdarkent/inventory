<?php

function get_navbar_menu() {
    return [
        [
            'key' => 'dashboard',
            'title' => 'Dashboard',
            'icon' => 'feather-airplay',
            'url' => 'dashboard',
            'children' => []
        ],
        [
            'key' => 'inventory',
            'title' => 'Inventory',
            'icon' => 'feather-package',
            'url' => 'javascript:void(0);',
            'children' => [
                [
                    'key' => 'log_data',
                    'title' => 'Log Data',
                    'url' => 'log_data'
                ],
                [
                    'key' => 'location',
                    'title' => 'Location',
                    'url' => 'location'
                ],
                [
                    'key' => 'in_out',
                    'title' => 'In/Out',
                    'url' => 'in_out'
                ]
            ]
        ]
    ];
}

function is_menu_active($current_page, $menu) {
    $basePage = strstr($current_page, '-', true) ?: $current_page;
    
    if ($menu['key'] === $current_page || $menu['key'] === $basePage) return true;
    if (!empty($menu['children'])) {
        foreach ($menu['children'] as $child) {
            if ($child['key'] === $current_page || $child['key'] === $basePage) return true;
        }
    }
    return false;
}

function is_submenu_active($current_page, $child) {
    $basePage = strstr($current_page, '-', true) ?: $current_page;
    return $child['key'] === $current_page || $child['key'] === $basePage;
}
