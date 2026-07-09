<?php

namespace App\Controllers;

use App\Models\PermissionModel;

class PermissionController extends BaseController
{
    public function index()
    {
        $permissionModel = new PermissionModel();
        $permissions = $permissionModel->findAll();

        return view('permissions/index', [
            'permissions' => $permissions,
            'breadcrumb'  => 'Permissions'
        ]);
    }

    public function store()
    {
        $permissionModel = new PermissionModel();

        $data = [
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description')
        ];

        $rules = [
            'name' => 'required|min_length[3]|max_length[100]|is_unique[permissions.name]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        if ($permissionModel->insert($data)) {
            return redirect()->to(base_url('permissions'))->with('success', 'Permission created successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create permission.');
    }

    public function delete($id)
    {
        $permissionModel = new PermissionModel();
        $permission = $permissionModel->find($id);
        if (!$permission) {
            return redirect()->to(base_url('permissions'))->with('error', 'Permission not found.');
        }

        if ($permissionModel->delete($id)) {
            return redirect()->to(base_url('permissions'))->with('success', 'Permission deleted successfully.');
        }

        return redirect()->to(base_url('permissions'))->with('error', 'Failed to delete permission.');
    }
}
