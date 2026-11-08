<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabela planos (RF12).
 */
class CriarTabelaPlanos extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'nome'          => ['type' => 'VARCHAR', 'constraint' => 80],
            'valor'         => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'periodicidade' => ['type' => 'ENUM', 'constraint' => ['MENSAL', 'TRIMESTRAL', 'SEMESTRAL']],
            'beneficios'    => ['type' => 'TEXT', 'null' => true],
            'situacao'      => ['type' => 'ENUM', 'constraint' => ['ATIVO', 'INATIVO'], 'default' => 'ATIVO'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('planos');
    }

    public function down(): void
    {
        $this->forge->dropTable('planos', true);
    }
}
