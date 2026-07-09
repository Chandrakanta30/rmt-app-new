<?php

namespace App\Models;

use CodeIgniter\Model;

class AsrModel extends Model
{
    protected $table      = 'form_asr_mapping';
    protected $primaryKey = 'id';

    protected $returnType    = 'array';
    protected $allowedFields = ['asr_no', 'form_id'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getWithFormDetails()
    {
        return $this->select('form_asr_mapping.*, forms.name as form_name')
            ->join('forms', 'forms.id = form_asr_mapping.form_id', 'left')
            ->orderBy('form_asr_mapping.id', 'DESC')
            ->findAll();
    }
}
