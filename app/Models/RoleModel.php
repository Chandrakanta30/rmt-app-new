<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table      = 'roles';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['name', 'description', 'created_at', 'updated_at'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getPermissions($roleId)
    {
        $db = \Config\Database::connect();
        return $db->table('role_permissions')
            ->select('permissions.*')
            ->join('permissions', 'permissions.id = role_permissions.permission_id')
            ->where('role_permissions.role_id', $roleId)
            ->get()
            ->getResultArray();
    }

    public function setPermissions($roleId, array $permissionIds)
    {
        $db = \Config\Database::connect();
        $db->table('role_permissions')->where('role_id', $roleId)->delete();

        if (empty($permissionIds)) {
            return true;
        }

        $data = [];
        foreach ($permissionIds as $pId) {
            $data[] = [
                'role_id'       => (int)$roleId,
                'permission_id' => (int)$pId
            ];
        }

        return $db->table('role_permissions')->insertBatch($data);
    }
}
