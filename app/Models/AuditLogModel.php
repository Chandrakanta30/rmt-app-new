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
        helper('audit');
        helper('auth');
        $user = current_user();

        $this->insert([
            'user_id'         => $user['id'] ?? null,
            'action'          => $action,
            'module'          => $module,
            'entity_id'       => $entityId,
            'remark'          => $remark,
            'request_payload' => audit_encode_body($payload),
            'current_record'  => audit_encode_body($currentRecord),
        ]);
    }

    /**
     * Chronological history for one entity, with each entry's "previous" state
     * derived from the prior entry's stored snapshot (no separate previous_record
     * column needed - every create/update/delete already snapshots current_record).
     *
     * @return array<int, array{action: string, remark: ?string, updated_by_name: ?string, created_at: string, previous: ?array, current: ?array, request_payload: ?array, current_record: ?array}>
     */
    public function getEntityHistory(string $module, int $entityId): array
    {
        helper('audit');

        $logs = $this->select('audit_logs.*, users.name as updated_by_name')
            ->join('users', 'users.id = audit_logs.user_id', 'left')
            ->where('audit_logs.module', $module)
            ->where('audit_logs.entity_id', $entityId)
            ->orderBy('audit_logs.created_at', 'ASC')
            ->orderBy('audit_logs.id', 'ASC')
            ->findAll();

        $history = [];
        $previousRecord = null;

        foreach ($logs as $log) {
            $currentRecord = $log['current_record'] !== null ? json_decode($log['current_record'], true) : null;

            $history[] = [
                'action'          => $log['action'],
                'remark'          => $log['remark'],
                'updated_by_name' => $log['updated_by_name'],
                'created_at'      => $log['created_at'],
                'previous'        => $previousRecord,
                'current'         => $currentRecord,
                'request_payload' => audit_decode_body($log['request_payload']),
                'current_record'  => $currentRecord,
            ];

            $previousRecord = $currentRecord;
        }

        return array_reverse($history);
    }
    public function getUserAuditLogs(int $userId, int $limit = 50, int $offset = 0): array
{
    return $this->select('audit_logs.*, users.name as performed_by')
        ->join('users', 'users.id = audit_logs.user_id', 'left')
        ->where('audit_logs.entity_id', $userId)
        ->where('audit_logs.module', 'user')
        ->orderBy('audit_logs.created_at', 'DESC')
        ->findAll($limit, $offset);
}

/**
 * Count total audit logs for a specific user
 * 
 * @param int $userId
 * @return int
 */
public function countUserAuditLogs(int $userId): int
{
    return $this->where('entity_id', $userId)
        ->where('module', 'user')
        ->countAllResults();
}
}
