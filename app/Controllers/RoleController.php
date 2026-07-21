<?php

namespace App\Controllers;

use App\Models\RoleModel;
use App\Models\PermissionModel;
use App\Models\AuditLogModel;

class RoleController extends BaseController
{
    public function index()
    {
        // $roleModel = new RoleModel();
        // $roles = $roleModel->findAll();

        // return view('roles/index', [
        //     'roles'      => $roles,
        //     'breadcrumb' => 'Roles & Permissions'
        // ]);

         $roleModel = new RoleModel();
    
    $page = $this->request->getGet('page') ?? 1;
    $perPage = 10;
    $offset = ($page - 1) * $perPage;
    
    $roles = $roleModel->findAll($perPage, $offset);
    $total = $roleModel->countAll();

    return view('roles/index', [
        'roles'      => $roles,
        'pagination' => [
            'page'        => (int)$page,
            'totalPages'  => ceil($total / $perPage),
            'total'       => $total
        ],
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

            // Log the creation
            $auditLogModel = new AuditLogModel();
            $auditLogModel->record(
                'create',
                'role',
                $roleId,
                'Role created: ' . $data['name'],
                $data,
                $roleModel->find($roleId)
            );

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

        $oldData = $role; // Store old data for audit

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

        $permissionIds = $this->request->getPost('permissions') ?: [];
        
        // Get old permissions for audit
        $oldPermissions = $roleModel->getPermissions($id);
        $oldPermissionIds = array_column($oldPermissions, 'id');

        if ($roleModel->update($id, $data)) {
            $roleModel->setPermissions($id, $permissionIds);

            // Log the update with detailed changes
            $auditLogModel = new AuditLogModel();
            
            // Prepare audit payload with changes
            $auditPayload = [
                'old_data' => $oldData,
                'new_data' => $data,
                'old_permissions' => $oldPermissionIds,
                'new_permissions' => $permissionIds
            ];
            
            $remark = 'Role updated';
            $changes = [];
            
            // Check for field changes
            foreach ($data as $key => $value) {
                if (isset($oldData[$key]) && $oldData[$key] != $value) {
                    $changes[] = $key . ': "' . $oldData[$key] . '" → "' . $value . '"';
                }
            }
            
            // Check for permission changes
            $removedPermissions = array_diff($oldPermissionIds, $permissionIds);
            $addedPermissions = array_diff($permissionIds, $oldPermissionIds);
            
            if (!empty($removedPermissions)) {
                $changes[] = 'Removed permissions: ' . implode(', ', $removedPermissions);
            }
            if (!empty($addedPermissions)) {
                $changes[] = 'Added permissions: ' . implode(', ', $addedPermissions);
            }
            
            if (!empty($changes)) {
                $remark = implode('; ', $changes);
            }
            
            $auditLogModel->record(
                'update',
                'role',
                $id,
                $remark,
                $auditPayload,
                $roleModel->find($id)
            );

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

        // Log the deletion
        $auditLogModel = new AuditLogModel();
        $auditLogModel->record(
            'delete',
            'role',
            $id,
            'Role deleted: ' . $role['name'],
            null,
            $role
        );

        if ($roleModel->delete($id)) {
            return redirect()->to(base_url('roles'))->with('success', 'Role deleted successfully.');
        }

        return redirect()->to(base_url('roles'))->with('error', 'Failed to delete role.');
    }

   public function auditLog($id)
{
    $roleModel = new RoleModel();
    $role = $roleModel->find($id);
    
    if (!$role) {
        return redirect()->to(base_url('roles'))->with('error', 'Role not found.');
    }

    $auditLogModel = new AuditLogModel();
    
    $page = $this->request->getGet('page') ?? 1;
    $perPage = 50;
    $offset = ($page - 1) * $perPage;
    
    // Join with users table to get the user name
    $logs = $auditLogModel->select('audit_logs.*, users.name as performed_by_name')
        ->join('users', 'users.id = audit_logs.user_id', 'left')
        ->where('audit_logs.module', 'role')
        ->where('audit_logs.entity_id', $id)
        ->orderBy('audit_logs.created_at', 'DESC')
        ->findAll($perPage, $offset);
    
    $total = $auditLogModel->where('module', 'role')
        ->where('entity_id', $id)
        ->countAllResults();

    helper('audit');
    $formattedChanges = [];
    
    foreach ($logs as $change) {
        $currentRecord = audit_decode_body($change['current_record']);
        $payload = audit_decode_body($change['request_payload']);
        
        $field = '-';
        $previousValue = '-';
        $currentValue = '-';
        
        if ($change['action'] === 'update' && $payload) {
            // Check for field changes from the remark
            if (!empty($change['remark'])) {
                $field = 'Role details';
                // Extract changes from remark if possible
                if (strpos($change['remark'], 'Updated') !== false) {
                    $field = 'Multiple fields';
                    $previousValue = 'See details';
                    $currentValue = 'See details';
                }
            }
            
            // Try to get specific field changes from payload
            if (isset($payload['old_data']) && isset($payload['new_data'])) {
                $oldData = $payload['old_data'];
                $newData = $payload['new_data'];
                $changedFields = [];
                
                foreach ($newData as $key => $value) {
                    if (!in_array($key, ['updated_at', 'id', 'created_at']) && isset($oldData[$key]) && $oldData[$key] != $value) {
                        $changedFields[] = $key;
                    }
                }
                
                if (!empty($changedFields)) {
                    $field = implode(', ', $changedFields);
                    $firstField = $changedFields[0] ?? null;
                    if ($firstField && isset($oldData[$firstField])) {
                        $previousValue = $oldData[$firstField] ?? '-';
                        $currentValue = $newData[$firstField] ?? '-';
                    }
                }
                
                // Check for permission changes
                if (isset($payload['old_permissions']) && isset($payload['new_permissions'])) {
                    $oldPerms = $payload['old_permissions'];
                    $newPerms = $payload['new_permissions'];
                    $removed = array_diff($oldPerms, $newPerms);
                    $added = array_diff($newPerms, $oldPerms);
                    
                    if (!empty($removed) || !empty($added)) {
                        $field = 'Permissions';
                        $previousValue = !empty($removed) ? 'Removed: ' . implode(', ', $removed) : '-';
                        $currentValue = !empty($added) ? 'Added: ' . implode(', ', $added) : '-';
                    }
                }
            }
        } elseif ($change['action'] === 'create') {
            $field = 'New Role';
            if ($currentRecord) {
                $currentValue = $currentRecord['name'] ?? 'Role created';
            }
        } elseif ($change['action'] === 'delete') {
            $field = 'Role';
            if ($currentRecord) {
                $previousValue = $currentRecord['name'] ?? 'Role deleted';
            }
        }
        
        $formattedChanges[] = [
            'action' => $change['action'],
            'field' => $field,
            'previous' => $previousValue,
            'current' => $currentValue,
            'performed_by' => $change['performed_by_name'] ?? $change['user_id'] ?? 'System', // Use the joined name
            'date' => $change['created_at'],
            'remark' => $change['remark'] ?? ''
        ];
    }

    return view('roles/audit_log', [
        'role'       => $role,
        'changes'    => $formattedChanges,
        'pagination' => [
            'page'        => (int)$page,
            'totalPages'  => ceil($total / $perPage),
            'total'       => $total
        ],
        'breadcrumb' => 'Role Audit Log'
    ]);
}
}