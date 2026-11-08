<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabela matriculas (RF07, RF08, RF09, RF23). A duplicidade de matricula ativa e checada no Model.
 */
class CriarTabelaMatriculas extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'aluno_id'            => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'turma_id'            => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'data_matricula'      => ['type' => 'DATE'],
            'situacao'            => ['type' => 'ENUM', 'constraint' => ['ATIVA', 'CANCELADA', 'TRANSFERIDA', 'FINALIZADA'], 'default' => 'ATIVA'],
            'data_encerramento'   => ['type' => 'DATE', 'null' => true],
            'certificado_emitido' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('aluno_id', 'alunos', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('turma_id', 'turmas', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('matriculas');
    }

    public function down(): void
    {
        $this->forge->dropTable('matriculas', true);
    }
}
