<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSoftDeleteToFormAsrMapping extends Migration
{
    public function up()
    {
        $this->forge->addColumn('form_asr_mapping', [
            'deleted_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'after'      => 'updated_at',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('form_asr_mapping', ['deleted_at']);
    }
}
