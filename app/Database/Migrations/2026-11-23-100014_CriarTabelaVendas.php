<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabelas vendas e venda_itens: registro de cada venda da loja e dos produtos vendidos (RF27, RF28).
 *
 * O preço de venda e o custo ficam copiados no item, para que reajustes futuros do produto
 * não alterem o histórico nem as análises de margem (RN13).
 */
class CriarTabelaVendas extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'aluno_id'        => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'usuario_id'      => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'forma_pagamento' => ['type' => 'ENUM', 'constraint' => ['PIX', 'CARTAO', 'DINHEIRO']],
            'valor_total'     => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'data_venda'      => ['type' => 'DATETIME'],
            'situacao'        => ['type' => 'ENUM', 'constraint' => ['CONCLUIDA', 'CANCELADA'], 'default' => 'CONCLUIDA'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('data_venda');
        $this->forge->addForeignKey('aluno_id', 'alunos', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('vendas');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'venda_id'       => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'produto_id'     => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'quantidade'     => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'preco_unitario' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'custo_unitario' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('venda_id', 'vendas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('produto_id', 'produtos', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('venda_itens');
    }

    public function down(): void
    {
        $this->forge->dropTable('venda_itens', true);
        $this->forge->dropTable('vendas', true);
    }
}
