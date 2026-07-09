<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\RoleModel;

class AuthController extends BaseController
{
    public function login()
    {
        helper(['form', 'url', 'auth']);
        
        if (is_logged_in()) {
            return redirect()->to(base_url('dashboard'));
        }

        return view('auth/login');
    }

    public function loginSubmit()
    {
        helper(['form', 'url', 'auth']);
        $session = session();
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();

        if ($user) {
            if (password_verify($password, $user['password'])) {
                // Get role name
                $roleModel = new RoleModel();
                $roleName = 'Guest';
                $permissions = [];
                
                $roleId = $user['role_id'] ?? null;

                
                if (!empty($roleId)) {
                    $role = $roleModel->find($roleId);
                   
                    if ($role) {
                        $roleName = $role['name'];
                        // Get permissions for role
                        $rolePerms = $roleModel->getPermissions($roleId);
                        $permissions = array_column($rolePerms, 'name');
                    }
                }

                $session->set([
                    'user_id'          => $user['id'],
                    'user_name'        => $user['name'],
                    'user_email'       => $user['email'],
                    'role_id'          => $roleId,
                    'role_name'        => $roleName,
                    'user_permissions' => $permissions,
                    'logged_in'        => true,
                ]);

                return redirect()->to(base_url('dashboard'))->with('success', 'Logged in successfully!');
            }
        }

        return redirect()->back()->withInput()->with('error', 'Invalid email or password.');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('login'))->with('success', 'Logged out successfully.');
    }
}
