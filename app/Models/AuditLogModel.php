<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table      = 'audit_logs';
    protected $primaryKey = 'id';

    protected $returnType    = 'array';
    protected $allowedFields = ['user_id', 'action', 'module', 'entity_id', 'remark'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    /**
     * Record one audit entry for the currently logged-in user.
     */
    public function record(string $action, string $module, ?int $entityId = null, ?string $remark = null): void
    {
        helper('auth');
        $user = current_user();

        $this->insert([
            'user_id'   => $user['id'] ?? null,
            'action'    => $action,
            'module'    => $module,
            'entity_id' => $entityId,
            'remark'    => $remark,
        ]);
    }
}
