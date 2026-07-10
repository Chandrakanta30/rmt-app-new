<?php

namespace App\Models;

use CodeIgniter\Model;

class AsrModel extends Model
{
    protected $table      = 'form_asr_mapping';
    protected $primaryKey = 'id';

    protected $returnType    = 'array';
    protected $allowedFields = ['asr_no', 'form_id', 'deleted_at', 'deleted_by'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getWithFormDetails()
    {
        return $this->select('form_asr_mapping.*, forms.name as form_name')
            ->join('forms', 'forms.id = form_asr_mapping.form_id', 'left')
            ->where('form_asr_mapping.deleted_at', null)
            ->orderBy('form_asr_mapping.id', 'DESC')
            ->findAll();
    }

    /**
     * Soft delete: stamps deleted_at/deleted_by instead of removing the row.
     * The reason for deletion is recorded separately in the audit log.
     */
    public function softDelete(int $id, int $deletedBy): bool
    {
        return $this->update($id, [
            'deleted_at' => date('Y-m-d H:i:s'),
            'deleted_by' => $deletedBy,
        ]);
    }
}
