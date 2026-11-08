<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabela turmas (RF06, RF22, RF23).
 */
class CriarTabelaTurmas extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'modalidade_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'professor_id'  => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'nome'          => ['type' => 'VARCHAR', 'constraint' => 80],
            'dias_semana'   => ['type' => 'VARCHAR', 'constraint' => 30],
            'hora_inicio'   => ['type' => 'TIME'],
            'hora_termino'  => ['type' => 'TIME'],
            'limite_vagas'  => ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true],
            'situacao'      => ['type' => 'ENUM', 'constraint' => ['ATIVA', 'SUSPENSA', 'ENCERRADA'], 'default' => 'ATIVA'],
            'data_inicio'   => ['type' => 'DATE'],
            'data_termino'  => ['type' => 'DATE', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('modalidade_id', 'modalidades', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('professor_id', 'professores', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('turmas');
    }

    public function down(): void
    {
        $this->forge->dropTable('turmas', true);
    }
}
