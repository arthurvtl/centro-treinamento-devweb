<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabela frequencias (RF10, RF11).
 */
class CriarTabelaFrequencias extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'matricula_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'data_aula'    => ['type' => 'DATE'],
            'situacao'     => ['type' => 'ENUM', 'constraint' => ['PRESENTE', 'FALTA']],
            'observacao'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        // Impede lancar duas vezes a mesma aula para a mesma matricula (HU04).
        $this->forge->addUniqueKey(['matricula_id', 'data_aula']);
        $this->forge->addForeignKey('matricula_id', 'matriculas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('frequencias');
    }

    public function down(): void
    {
        $this->forge->dropTable('frequencias', true);
    }
}
