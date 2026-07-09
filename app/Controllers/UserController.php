<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\RoleModel;

class UserController extends BaseController
{
    public function index()
    {
        $userModel = new UserModel();
        $users = $userModel->getWithRole();

        return view('users/index', [
            'users'      => $users,
            'breadcrumb' => 'Users Management'
        ]);
    }

    public function create()
    {
        $roleModel = new RoleModel();
        $roles = $roleModel->findAll();

        return view('users/create', [
            'roles'      => $roles,
            'breadcrumb' => 'Create User'
        ]);
    }

    public function store()
    {
        $userModel = new UserModel();

        $data = [
            'name'     => $this->request->getPost('name'),
            'email'    => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
            'role_id'  => $this->request->getPost('role_id') ?: null
        ];

        // Basic validation rules
        $rules = [
            'name'     => 'required|min_length[3]|max_length[100]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        if ($userModel->insert($data)) {
            return redirect()->to(base_url('users'))->with('success', 'User created successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create user.');
    }

    public function edit($id)
    {
        $userModel = new UserModel();
        $roleModel = new RoleModel();

        $user = $userModel->find($id);
        if (!$user) {
            return redirect()->to(base_url('users'))->with('error', 'User not found.');
        }

        $roles = $roleModel->findAll();

        return view('users/edit', [
            'user'       => $user,
            'roles'      => $roles,
            'breadcrumb' => 'Edit User'
        ]);
    }

    public function update($id)
    {
        $userModel = new UserModel();

        $user = $userModel->find($id);
        if (!$user) {
            return redirect()->to(base_url('users'))->with('error', 'User not found.');
        }

        $data = [
            'name'    => $this->request->getPost('name'),
            'email'   => $this->request->getPost('email'),
            'role_id' => $this->request->getPost('role_id') ?: null
        ];

        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $data['password'] = $password;
        }

        // Basic validation rules (ignore unique check for current email)
        $rules = [
            'name'  => 'required|min_length[3]|max_length[100]',
            'email' => "required|valid_email|is_unique[users.email,id,{$id}]",
        ];

        if (!empty($password)) {
            $rules['password'] = 'min_length[6]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        if ($userModel->update($id, $data)) {
            return redirect()->to(base_url('users'))->with('success', 'User updated successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update user.');
    }

    public function delete($id)
    {
        $userModel = new UserModel();
        $user = $userModel->find($id);
        if (!$user) {
            return redirect()->to(base_url('users'))->with('error', 'User not found.');
        }

        // Prevent admin deleting themselves
        if ($user['id'] == session()->get('user_id')) {
            return redirect()->to(base_url('users'))->with('error', 'You cannot delete your own account.');
        }

        if ($userModel->delete($id)) {
            return redirect()->to(base_url('users'))->with('success', 'User deleted successfully.');
        }

        return redirect()->to(base_url('users'))->with('error', 'Failed to delete user.');
    }
}
