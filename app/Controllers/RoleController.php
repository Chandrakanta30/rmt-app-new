<?php

namespace App\Controllers;

use App\Models\RoleModel;
use App\Models\PermissionModel;

class RoleController extends BaseController
{
    public function index()
    {
        $roleModel = new RoleModel();
        $roles = $roleModel->findAll();

        return view('roles/index', [
            'roles'      => $roles,
            'breadcrumb' => 'Roles & Permissions'
        ]);
    }

    public function create()
    {
        $permissionModel = new PermissionModel();
        $permissions = $permissionModel->findAll();

        return view('roles/create', [
            'permissions' => $permissions,
            'breadcrumb'  => 'Create Role'
        ]);
    }

    public function store()
    {
        $roleModel = new RoleModel();

        $data = [
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description')
        ];

        $rules = [
            'name' => 'required|min_length[3]|max_length[100]|is_unique[roles.name]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $roleId = $roleModel->insert($data);
        if ($roleId) {
            $permissionIds = $this->request->getPost('permissions') ?: [];
            $roleModel->setPermissions($roleId, $permissionIds);

            return redirect()->to(base_url('roles'))->with('success', 'Role created successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create role.');
    }

    public function edit($id)
    {
        $roleModel = new RoleModel();
        $permissionModel = new PermissionModel();

        $role = $roleModel->find($id);
        if (!$role) {
            return redirect()->to(base_url('roles'))->with('error', 'Role not found.');
        }

        $permissions = $permissionModel->findAll();
        $rolePermissions = $roleModel->getPermissions($id);
        $rolePermissionIds = array_column($rolePermissions, 'id');

        return view('roles/edit', [
            'role'              => $role,
            'permissions'       => $permissions,
            'rolePermissionIds' => $rolePermissionIds,
            'breadcrumb'        => 'Edit Role'
        ]);
    }

    public function update($id)
    {
        $roleModel = new RoleModel();

        $role = $roleModel->find($id);
        if (!$role) {
            return redirect()->to(base_url('roles'))->with('error', 'Role not found.');
        }

        $data = [
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description')
        ];

        $rules = [
            'name' => "required|min_length[3]|max_length[100]|is_unique[roles.name,id,{$id}]"
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        if ($roleModel->update($id, $data)) {
            $permissionIds = $this->request->getPost('permissions') ?: [];
            $roleModel->setPermissions($id, $permissionIds);

            return redirect()->to(base_url('roles'))->with('success', 'Role updated successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update role.');
    }

    public function delete($id)
    {
        $roleModel = new RoleModel();
        $role = $roleModel->find($id);
        if (!$role) {
            return redirect()->to(base_url('roles'))->with('error', 'Role not found.');
        }

        // Prevent deleting Admin role
        if ($role['name'] === 'Admin') {
            return redirect()->to(base_url('roles'))->with('error', 'The Admin role cannot be deleted.');
        }

        if ($roleModel->delete($id)) {
            return redirect()->to(base_url('roles'))->with('success', 'Role deleted successfully.');
        }

        return redirect()->to(base_url('roles'))->with('error', 'Failed to delete role.');
    }
}
