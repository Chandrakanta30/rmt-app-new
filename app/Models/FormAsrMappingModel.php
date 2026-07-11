<?php

namespace App\Models;

use CodeIgniter\Model;

class FormAsrMappingModel extends Model
{
    protected $table = 'form_asr_mapping';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['asr_no', 'form_id', 'created_at', 'updated_at'];
}
