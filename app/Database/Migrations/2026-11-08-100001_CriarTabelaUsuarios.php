<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabela usuarios: credenciais e perfil de acesso (RF20, RF21).
 */
class CriarTabelaUsuarios extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'nome'        => ['type' => 'VARCHAR', 'constraint' => 120],
            'login_email' => ['type' => 'VARCHAR', 'constraint' => 150],
            'senha_hash'  => ['type' => 'VARCHAR', 'constraint' => 255],
            'perfil'      => ['type' => 'ENUM', 'constraint' => ['PROPRIETARIO', 'RECEPCIONISTA']],
            'situacao'    => ['type' => 'ENUM', 'constraint' => ['ATIVO', 'INATIVO'], 'default' => 'ATIVO'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('login_email');
        $this->forge->createTable('usuarios');
    }

    public function down(): void
    {
        $this->forge->dropTable('usuarios', true);
    }
}
