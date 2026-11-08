<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabela associativa professor x modalidade habilitada (RF04, RN03).
 */
class CriarTabelaProfessorModalidade extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'professor_id'  => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'modalidade_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        // Chave composta: um professor não é habilitado duas vezes na mesma modalidade (RN03).
        $this->forge->addKey(['professor_id', 'modalidade_id'], true);
        $this->forge->addForeignKey('professor_id', 'professores', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('modalidade_id', 'modalidades', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('professor_modalidade');
    }

    public function down(): void
    {
        $this->forge->dropTable('professor_modalidade', true);
    }
}
