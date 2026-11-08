<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabela mensalidades (RF14, RF24, RF25, RN07, RN11).
 */
class CriarTabelaMensalidades extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'aluno_plano_id'  => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'valor'           => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'vencimento'      => ['type' => 'DATE'],
            'data_pagamento'  => ['type' => 'DATE', 'null' => true],
            'forma_pagamento' => ['type' => 'ENUM', 'constraint' => ['PIX', 'CARTAO', 'DINHEIRO'], 'null' => true],
            'situacao'        => ['type' => 'ENUM', 'constraint' => ['EM_ABERTO', 'PAGO', 'ATRASADO'], 'default' => 'EM_ABERTO'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('aluno_plano_id', 'aluno_planos', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('mensalidades');
    }

    public function down(): void
    {
        $this->forge->dropTable('mensalidades', true);
    }
}
