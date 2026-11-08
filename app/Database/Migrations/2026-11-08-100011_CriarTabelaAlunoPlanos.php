<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabela aluno_planos: historico de planos; RN08 (um ativo) e garantida no Model (RF13).
 */
class CriarTabelaAlunoPlanos extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'aluno_id'    => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'plano_id'    => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'data_inicio' => ['type' => 'DATE'],
            'data_fim'    => ['type' => 'DATE', 'null' => true],
            'situacao'    => ['type' => 'ENUM', 'constraint' => ['ATIVO', 'ENCERRADO'], 'default' => 'ATIVO'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('aluno_id', 'alunos', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('plano_id', 'planos', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('aluno_planos');
    }

    public function down(): void
    {
        $this->forge->dropTable('aluno_planos', true);
    }
}
