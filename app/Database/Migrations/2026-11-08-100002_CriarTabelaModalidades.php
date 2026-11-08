<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabela modalidades: capacidade_padrao sustenta RN01 e RN02 (RF05).
 */
class CriarTabelaModalidades extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'nome'              => ['type' => 'VARCHAR', 'constraint' => 80],
            'descricao'         => ['type' => 'TEXT', 'null' => true],
            'categoria'         => ['type' => 'ENUM', 'constraint' => ['LUTA', 'ESPORTE_COLETIVO', 'INDIVIDUAL']],
            'capacidade_padrao' => ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('nome');
        $this->forge->createTable('modalidades');
    }

    public function down(): void
    {
        $this->forge->dropTable('modalidades', true);
    }
}
