<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabela professores (RF04).
 */
class CriarTabelaProfessores extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'nome'     => ['type' => 'VARCHAR', 'constraint' => 120],
            'cpf'      => ['type' => 'CHAR', 'constraint' => 11],
            'telefone' => ['type' => 'VARCHAR', 'constraint' => 20],
            'email'    => ['type' => 'VARCHAR', 'constraint' => 150],
            'situacao' => ['type' => 'ENUM', 'constraint' => ['ATIVO', 'INATIVO'], 'default' => 'ATIVO'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('cpf');
        $this->forge->createTable('professores');
    }

    public function down(): void
    {
        $this->forge->dropTable('professores', true);
    }
}
