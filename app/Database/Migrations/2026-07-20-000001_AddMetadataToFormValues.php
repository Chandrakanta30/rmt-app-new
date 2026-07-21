<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMetadataToFormValues extends Migration
{
    public function up()
    {
        $fields = [
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'values',
            ],
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'created_at',
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'created_by',
            ],
            'updated_by' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'updated_at',
            ],
            'reviewed_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'updated_by',
            ],
            'reviewed_by' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'reviewed_at',
            ],
        ];

        $this->forge->addColumn('form_values', $fields);

        // Add Foreign Keys if users table exists
        $db = \Config\Database::connect();
        if ($db->tableExists('users')) {
            $db->query("ALTER TABLE `form_values` ADD CONSTRAINT `fk_form_values_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;");
            $db->query("ALTER TABLE `form_values` ADD CONSTRAINT `fk_form_values_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;");
            $db->query("ALTER TABLE `form_values` ADD CONSTRAINT `fk_form_values_reviewed_by` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;");
        }
    }

    public function down()
    {
        $this->forge->dropColumn('form_values', ['created_at', 'created_by', 'updated_at', 'updated_by', 'reviewed_at', 'reviewed_by']);
    }
}
