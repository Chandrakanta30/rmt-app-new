<?php

namespace App\Models;

use CodeIgniter\Model;

class FormModel extends Model
{
    protected $table = 'forms';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
        // protected $allowedFields = ['name', 'form_key', 'table', 'reviewed', 'approved', 'status'];
        protected $allowedFields = [
        'form_key', 
        'name', 
        'Group_name',  // Notice the capital G
        'status'
    ];
    protected $useTimestamps = true;  // This handles created_at and updated_at automatically
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

}