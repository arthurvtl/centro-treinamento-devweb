<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabela produtos: itens vendidos na loja do centro, como roupas, acessórios e suplementos (RF26).
 */
class CriarTabelaProdutos extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'nome'           => ['type' => 'VARCHAR', 'constraint' => 80],
            'categoria'      => ['type' => 'ENUM', 'constraint' => ['VESTUARIO', 'EQUIPAMENTO', 'SUPLEMENTO', 'BEBIDA', 'OUTROS']],
            'preco_custo'    => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'preco_venda'    => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'estoque_atual'  => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'default' => 0],
            'estoque_minimo' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'default' => 0],
            'situacao'       => ['type' => 'ENUM', 'constraint' => ['ATIVO', 'INATIVO'], 'default' => 'ATIVO'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('produtos');
    }

    public function down(): void
    {
        $this->forge->dropTable('produtos', true);
    }
}
