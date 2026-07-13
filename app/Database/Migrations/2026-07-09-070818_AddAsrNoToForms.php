<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAsrNoToForms extends Migration
{
    public function up()
    {
        $this->forge->addColumn('forms', [
            'asr_no' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'name'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('forms', 'asr_no');
    }
}
