<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPayloadSnapshotToAuditLogs extends Migration
{
    public function up()
    {
        $this->forge->addColumn('audit_logs', [
            'request_payload' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'remark',
            ],
            'current_record' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'request_payload',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('audit_logs', ['request_payload', 'current_record']);
    }
}
