<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['name', 'email', 'password', 'role_id', 'created_at', 'updated_at'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Callbacks
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password']) && !empty($data['data']['password'])) {
            // Check if it's already hashed
            $info = password_get_info($data['data']['password']);
            if ($info['algo'] === 0) { // not hashed
                $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_BCRYPT);
            }
        }
        return $data;
    }
    
    public function getWithRole($id = null)
    {
        if ($id === null) {
            return $this->select('users.*, roles.name as role_name')
                        ->join('roles', 'roles.id = users.role_id', 'left')
                        ->findAll();
        }
        
        return $this->select('users.*, roles.name as role_name')
                    ->join('roles', 'roles.id = users.role_id', 'left')
                    ->where('users.id', $id)
                    ->first();
    }
}
