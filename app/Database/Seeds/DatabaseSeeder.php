<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Popula o banco com dados de demonstração do Sistema CT.
 *
 * A ordem respeita as chaves estrangeiras (pais antes dos filhos).
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Executa os seeders na ordem de dependência das tabelas.
     */
    public function run(): void
    {
        $this->call('UsuarioSeeder');
        $this->call('ModalidadeSeeder');
        $this->call('ProfessorSeeder');
        $this->call('TurmaSeeder');
        $this->call('AlunoSeeder');
        $this->call('PlanoSeeder');
    }
}
