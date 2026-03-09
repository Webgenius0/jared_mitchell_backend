<?php

namespace App\Helpers;

use Illuminate\Support\Collection;

class PermissionHelper
{
    /**
     * Group permissions by the last word in their name
     * e.g., "view users" → group "Users", "edit roles" → group "Roles"
     */
    public static function groupPermissionsByModule(Collection $permissions): array
    {
        $grouped = [];

        foreach ($permissions as $permission) {
            $words = explode(' ', $permission->name);
            $module = ucfirst(end($words));

            if (!isset($grouped[$module])) {
                $grouped[$module] = [];
            }

            $grouped[$module][] = $permission;
        }

        ksort($grouped);

        return $grouped;
    }
}
