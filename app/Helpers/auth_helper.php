<?php

use App\Models\RoleModel;

if (!function_exists('is_logged_in')) {
    function is_logged_in(): bool
    {
        return session()->has('logged_in') && session()->get('logged_in') === true;
    }
}

if (!function_exists('current_user')) {
    function current_user(): ?array
    {
        if (!is_logged_in()) {
            return null;
        }
        return [
            'id'        => session()->get('user_id'),
            'name'      => session()->get('user_name'),
            'email'     => session()->get('user_email'),
            'role_id'   => session()->get('role_id'),
            'role_name' => session()->get('role_name'),
        ];
    }
}

if (!function_exists('has_permission')) {
    function has_permission(string $permission): bool
    {
        if (!is_logged_in()) {
            return false;
        }

        // Admin has all permissions
        if (session()->get('role_name') === 'Admin') {
            return true;
        }

        $permissions = session()->get('user_permissions') ?? [];
        return in_array($permission, $permissions, true);
    }
}

if (!function_exists('has_role')) {
    function has_role(string $role): bool
    {
        if (!is_logged_in()) {
            return false;
        }
        return session()->get('role_name') === $role;
    }
}
