<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Usuários de demonstração (RF20, RF21). Senha de ambos: ct123456.
 */
class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $agora = date('Y-m-d H:i:s');
        $senhaHash = password_hash('ct123456', PASSWORD_DEFAULT);

        $this->db->table('usuarios')->insertBatch([
            ['nome' => 'Marcos Proprietário', 'login_email' => 'proprietario@ct.local', 'senha_hash' => $senhaHash,
             'perfil' => 'PROPRIETARIO', 'situacao' => 'ATIVO', 'created_at' => $agora, 'updated_at' => $agora],
            ['nome' => 'Camila Santos', 'login_email' => 'recepcao@ct.local', 'senha_hash' => $senhaHash,
             'perfil' => 'RECEPCIONISTA', 'situacao' => 'ATIVO', 'created_at' => $agora, 'updated_at' => $agora],
        ]);
    }
}
