<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabela alunos (RF01, RF02, RN06).
 */
class CriarTabelaAlunos extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'codigo'             => ['type' => 'CHAR', 'constraint' => 8],
            'nome'               => ['type' => 'VARCHAR', 'constraint' => 120],
            'data_nascimento'    => ['type' => 'DATE'],
            'cpf'                => ['type' => 'CHAR', 'constraint' => 11],
            'telefone'           => ['type' => 'VARCHAR', 'constraint' => 20],
            'email'              => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'endereco'           => ['type' => 'VARCHAR', 'constraint' => 255],
            'data_matricula'     => ['type' => 'DATE'],
            'condicao_saude'     => ['type' => 'TEXT', 'null' => true],
            'contato_emergencia' => ['type' => 'VARCHAR', 'constraint' => 120],
            'situacao'           => ['type' => 'ENUM', 'constraint' => ['ATIVO', 'INATIVO'], 'default' => 'ATIVO'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('codigo');
        $this->forge->addUniqueKey('cpf');
        $this->forge->createTable('alunos');
    }

    public function down(): void
    {
        $this->forge->dropTable('alunos', true);
    }
}
