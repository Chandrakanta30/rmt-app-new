<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table      = 'audit_logs';
    protected $primaryKey = 'id';

    protected $returnType    = 'array';
    protected $allowedFields = ['user_id', 'action', 'module', 'entity_id', 'remark', 'request_payload', 'current_record'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    /**
     * Fields that must never be written into the audit trail even if a caller
     * passes them in as part of $payload or $currentRecord.
     */
    private const SENSITIVE_KEYS = ['password', 'password_confirm', 'token', 'csrf_test_name'];

    /**
     * Record one audit entry for the currently logged-in user.
     *
     * @param array|null $payload       Raw request input for this action (e.g. what the user submitted).
     * @param array|null $currentRecord Snapshot of the entity's full row state at the time of the action.
     */
    public function record(
        string $action,
        string $module,
        ?int $entityId = null,
        ?string $remark = null,
        ?array $payload = null,
        ?array $currentRecord = null
    ): void {
        helper('auth');
        $user = current_user();

        $this->insert([
            'user_id'         => $user['id'] ?? null,
            'action'          => $action,
            'module'          => $module,
            'entity_id'       => $entityId,
            'remark'          => $remark,
            'request_payload' => $payload === null ? null : json_encode($this->stripSensitive($payload)),
            'current_record'  => $currentRecord === null ? null : json_encode($this->stripSensitive($currentRecord)),
        ]);
    }

    private function stripSensitive(array $data): array
    {
        foreach (self::SENSITIVE_KEYS as $key) {
            unset($data[$key]);
        }

        return $data;
    }
}
