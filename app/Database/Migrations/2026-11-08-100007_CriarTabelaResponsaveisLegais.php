<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabela responsaveis_legais (RF03, RN04).
 */
class CriarTabelaResponsaveisLegais extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'aluno_id'   => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'nome'       => ['type' => 'VARCHAR', 'constraint' => 120],
            'cpf'        => ['type' => 'CHAR', 'constraint' => 11],
            'telefone'   => ['type' => 'VARCHAR', 'constraint' => 20],
            'email'      => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'parentesco' => ['type' => 'VARCHAR', 'constraint' => 30],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        // UNIQUE em aluno_id: relação 1 para 0..1 com o aluno (RF03, RN04).
        $this->forge->addUniqueKey('aluno_id');
        $this->forge->addForeignKey('aluno_id', 'alunos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('responsaveis_legais');
    }

    public function down(): void
    {
        $this->forge->dropTable('responsaveis_legais', true);
    }
}
