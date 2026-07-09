<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DbSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        // 1. Insert permissions
        $permissions = [
            [
                'name'        => 'manage_users',
                'description' => 'Create, edit, delete users',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'name'        => 'manage_roles',
                'description' => 'Create, edit, delete roles and map permissions',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'name'        => 'manage_permissions',
                'description' => 'Create, edit, delete permissions',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'name'        => 'view_forms',
                'description' => 'Access and view form details',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'name'        => 'submit_data',
                'description' => 'Submit laboratory form data',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'name'        => 'view_submissions',
                'description' => 'View historical form submissions/reports',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($permissions as $perm) {
            $existing = $db->table('permissions')->where('name', $perm['name'])->get()->getRow();
            if (!$existing) {
                $db->table('permissions')->insert($perm);
            }
        }

        // 2. Insert roles
        $roles = [
            [
                'name'        => 'Admin',
                'description' => 'Administrator with full system control',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'name'        => 'Analyst',
                'description' => 'Laboratory analyst who fills forms and submits data',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'name'        => 'Reviewer',
                'description' => 'Lab reviewer who verifies submissions and views reports',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($roles as $role) {
            $existing = $db->table('roles')->where('name', $role['name'])->get()->getRow();
            if (!$existing) {
                $db->table('roles')->insert($role);
            }
        }

        // 3. Map permissions to roles
        $adminRole = $db->table('roles')->where('name', 'Admin')->get()->getRow();
        $analystRole = $db->table('roles')->where('name', 'Analyst')->get()->getRow();
        $reviewerRole = $db->table('roles')->where('name', 'Reviewer')->get()->getRow();

        $allPerms = $db->table('permissions')->get()->getResultArray();
        $permMap = [];
        foreach ($allPerms as $p) {
            $permMap[$p['name']] = $p['id'];
        }

        // Admin: all permissions
        if ($adminRole) {
            foreach ($allPerms as $p) {
                $existing = $db->table('role_permissions')
                    ->where('role_id', $adminRole->id)
                    ->where('permission_id', $p['id'])
                    ->get()
                    ->getRow();
                if (!$existing) {
                    $db->table('role_permissions')->insert([
                        'role_id'       => $adminRole->id,
                        'permission_id' => $p['id']
                    ]);
                }
            }
        }

        // Analyst: view_forms, submit_data
        if ($analystRole) {
            $analystPermNames = ['view_forms', 'submit_data'];
            foreach ($analystPermNames as $name) {
                if (isset($permMap[$name])) {
                    $pid = $permMap[$name];
                    $existing = $db->table('role_permissions')
                        ->where('role_id', $analystRole->id)
                        ->where('permission_id', $pid)
                        ->get()
                        ->getRow();
                    if (!$existing) {
                        $db->table('role_permissions')->insert([
                            'role_id'       => $analystRole->id,
                            'permission_id' => $pid
                        ]);
                    }
                }
            }
        }

        // Reviewer: view_forms, submit_data, view_submissions
        if ($reviewerRole) {
            $reviewerPermNames = ['view_forms', 'submit_data', 'view_submissions'];
            foreach ($reviewerPermNames as $name) {
                if (isset($permMap[$name])) {
                    $pid = $permMap[$name];
                    $existing = $db->table('role_permissions')
                        ->where('role_id', $reviewerRole->id)
                        ->where('permission_id', $pid)
                        ->get()
                        ->getRow();
                    if (!$existing) {
                        $db->table('role_permissions')->insert([
                            'role_id'       => $reviewerRole->id,
                            'permission_id' => $pid
                        ]);
                    }
                }
            }
        }

        // 4. Default Admin User (only if it doesn't exist)
        if ($adminRole) {
            $adminUser = $db->table('users')->where('email', 'admin@gmail.com')->get()->getRow();
            if (!$adminUser) {
                $db->table('users')->insert([
                    'name'       => 'Administrator',
                    'email'      => 'admin@gmail.com',
                    'password'   => password_hash('Password123', PASSWORD_BCRYPT),
                    'role_id'    => $adminRole->id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        // 5. Update existing users to have the Analyst role
        if ($analystRole) {
            $db->table('users')
                ->where('role_id IS NULL')
                ->update(['role_id' => $analystRole->id]);
        }
    }
}
