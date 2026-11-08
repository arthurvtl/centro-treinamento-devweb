<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Usuários de demonstração (RF20, RF21): `dono@ct.local` (perfil proprietário) e `recepcao@ct.local`
 * (perfil recepcionista). Senha de ambos: ct123456. Os nomes são neutros de propósito; o que define
 * as permissões é o perfil gravado no banco, não o nome.
 */
class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $agora = date('Y-m-d H:i:s');
        $senhaHash = password_hash('ct123456', PASSWORD_DEFAULT);

        $this->db->table('usuarios')->insertBatch([
            ['nome' => 'Dono', 'login_email' => 'dono@ct.local', 'senha_hash' => $senhaHash,
             'perfil' => 'PROPRIETARIO', 'situacao' => 'ATIVO', 'created_at' => $agora, 'updated_at' => $agora],
            ['nome' => 'Recepção', 'login_email' => 'recepcao@ct.local', 'senha_hash' => $senhaHash,
             'perfil' => 'RECEPCIONISTA', 'situacao' => 'ATIVO', 'created_at' => $agora, 'updated_at' => $agora],
        ]);
    }
}
