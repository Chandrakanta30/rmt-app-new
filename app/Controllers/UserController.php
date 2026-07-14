<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\AuditLogModel;

class UserController extends BaseController
{
    public function index()
    {
        // $userModel = new UserModel();
        // $users = $userModel->getWithRole();

        // return view('users/index', [
        //     'users'      => $users,
        //     'breadcrumb' => 'Users Management'
        // ]);
        $userModel = new UserModel();

        $page = $this->request->getGet('page') ?? 1;
        $perPage = 10;

        // Get all users using existing getWithRole() method
        $allUsers = $userModel->getWithRole();
        $total = count($allUsers);

        $offset = ($page - 1) * $perPage;
        $users = array_slice($allUsers, $offset, $perPage);

        return view('users/index', [
            'users'      => $users,
            'pagination' => [
                'page'        => (int)$page,
                'totalPages'  => ceil($total / $perPage),
                'total'       => $total
            ],
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
            // Log the creation
            $auditLogModel = new AuditLogModel();
            $userId = $userModel->getInsertID();
            $auditLogModel->record(
                'create',
                'user',
                $userId,
                'User created: ' . $data['name'],
                $data,
                $userModel->find($userId)
            );

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
            // Log the update with detailed field changes
            $auditLogModel = new AuditLogModel();

            // Find what actually changed
            $updatedUser = $userModel->find($id);
            $changedFields = [];
            $fieldDetails = [];

            foreach ($data as $key => $value) {
                if ($key !== 'password' && isset($user[$key]) && $user[$key] != $value) {
                    $changedFields[] = $key;
                    $fieldDetails[] = $key . ': "' . $user[$key] . '" → "' . $value . '"';
                }
            }

            $remark = 'User updated';
            if (!empty($fieldDetails)) {
                $remark = 'Updated fields: ' . implode(', ', $fieldDetails);
            }

            // Store the old and new data in the payload for better audit
            $auditPayload = [
                'old_data' => $user,
                'new_data' => $data,
                'changed_fields' => $changedFields
            ];

            $auditLogModel->record(
                'update',
                'user',
                $id,
                $remark,
                $auditPayload,
                $updatedUser
            );

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

        // Log the deletion before deleting
        $auditLogModel = new AuditLogModel();
        $auditLogModel->record(
            'delete',
            'user',
            $id,
            'User deleted: ' . $user['name'],
            null,
            $user
        );

        if ($userModel->delete($id)) {
            return redirect()->to(base_url('users'))->with('success', 'User deleted successfully.');
        }

        return redirect()->to(base_url('users'))->with('error', 'Failed to delete user.');
    }

    public function auditLog($id)
    {
        $userModel = new UserModel();
        $user = $userModel->getWithRole($id);

        if (!$user) {
            return redirect()->to(base_url('users'))->with('error', 'User not found.');
        }

        $auditLogModel = new AuditLogModel();

        $page = $this->request->getGet('page') ?? 1;
        $perPage = 50;
        $offset = ($page - 1) * $perPage;

        $logs = $auditLogModel->getUserAuditLogs($id, $perPage, $offset);
        $total = $auditLogModel->countUserAuditLogs($id);

        helper('audit');
        $formattedChanges = [];

        foreach ($logs as $change) {
            $currentRecord = audit_decode_body($change['current_record']);
            $payload = audit_decode_body($change['request_payload']);

            $field = '-';
            $previousValue = '-';
            $currentValue = '-';

            if ($change['action'] === 'update' && $payload) {
                // Check if we have old_data and new_data in payload
                if (isset($payload['old_data']) && isset($payload['new_data'])) {
                    $oldData = $payload['old_data'];
                    $newData = $payload['new_data'];
                    $changedFields = $payload['changed_fields'] ?? [];

                    if (!empty($changedFields)) {
                        $field = implode(', ', $changedFields);

                        // Show the first changed field's values
                        $firstField = $changedFields[0] ?? null;
                        if ($firstField) {
                            $previousValue = $oldData[$firstField] ?? '-';
                            $currentValue = $newData[$firstField] ?? '-';

                            // If multiple fields, show a summary
                            if (count($changedFields) > 1) {
                                $field = count($changedFields) . ' fields changed';
                                $previousValue = 'Multiple fields';
                                $currentValue = 'Multiple fields';
                            }
                        }
                    }
                } else {
                    // Fallback: try to get from the remark
                    if (!empty($change['remark']) && strpos($change['remark'], 'Updated fields:') !== false) {
                        $field = 'User details';
                        $previousValue = 'See remark';
                        $currentValue = 'See remark';
                    }
                }
            } elseif ($change['action'] === 'create') {
                $field = 'New User Account';
                if ($currentRecord) {
                    $currentValue = $currentRecord['name'] ?? 'User created';
                    $previousValue = '-';
                }
            } elseif ($change['action'] === 'delete') {
                $field = 'User Account';
                if ($currentRecord) {
                    $previousValue = $currentRecord['name'] ?? 'User deleted';
                    $currentValue = '-';
                }
            }

            $formattedChanges[] = [
                'action' => $change['action'],
                'field' => $field,
                'previous' => $previousValue,
                'current' => $currentValue,
                'performed_by' => $change['performed_by'] ?? $change['user_id'] ?? 'System',
                'date' => $change['created_at'],
                'remark' => $change['remark'] ?? ''
            ];
        }

        return view('users/audit_log', [
            'user'       => $user,
            'changes'    => $formattedChanges,
            'pagination' => [
                'page'        => (int)$page,
                'totalPages'  => ceil($total / $perPage),
                'total'       => $total
            ],
            'breadcrumb' => 'User Audit Log'
        ]);
    }
}
