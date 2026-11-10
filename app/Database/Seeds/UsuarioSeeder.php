<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Usuários de demonstração (RF20, RF21): login `dono` (perfil proprietário) e login `recepcao`
 * (perfil recepcionista), os dois com a senha `admin`. São credenciais SÓ de demonstração: troque
 * antes de usar o sistema com dados reais. A coluna continua `login_email`, mas guarda o
 * identificador de acesso (e-mail ou usuário simples). Os nomes são neutros de propósito; o que
 * define as permissões é o perfil gravado no banco, não o nome.
 */
class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $agora = date('Y-m-d H:i:s');
        $senhaHash = password_hash('admin', PASSWORD_DEFAULT);

        $this->db->table('usuarios')->insertBatch([
            ['nome' => 'Dono', 'login_email' => 'dono', 'senha_hash' => $senhaHash,
             'perfil' => 'PROPRIETARIO', 'situacao' => 'ATIVO', 'created_at' => $agora, 'updated_at' => $agora],
            ['nome' => 'Recepção', 'login_email' => 'recepcao', 'senha_hash' => $senhaHash,
             'perfil' => 'RECEPCIONISTA', 'situacao' => 'ATIVO', 'created_at' => $agora, 'updated_at' => $agora],
        ]);
    }
}
