<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Professores e suas habilitações por modalidade (RF04, RN03).
 */
class ProfessorSeeder extends Seeder
{
    public function run(): void
    {
        $agora = date('Y-m-d H:i:s');
        $tabela = $this->db->table('professores');

        $tabela->insertBatch([
            ['nome' => 'Marcão Gomes', 'cpf' => CpfDemonstracao::gerar('111222333'), 'telefone' => '(27) 99999-0001',
             'email' => 'marcao@ct.local', 'situacao' => 'ATIVO', 'created_at' => $agora, 'updated_at' => $agora],
            ['nome' => 'Vanclei Moura', 'cpf' => CpfDemonstracao::gerar('222333444'), 'telefone' => '(27) 99999-0002',
             'email' => 'vanclei@ct.local', 'situacao' => 'ATIVO', 'created_at' => $agora, 'updated_at' => $agora],
            ['nome' => 'Renata Lima', 'cpf' => CpfDemonstracao::gerar('333444555'), 'telefone' => '(27) 99999-0003',
             'email' => 'renata@ct.local', 'situacao' => 'ATIVO', 'created_at' => $agora, 'updated_at' => $agora],
        ]);

        // Marcão: jiu-jitsu e muay thai; Vanclei: muay thai; Renata: vôlei e futsal.
        $this->db->table('professor_modalidade')->insertBatch([
            ['professor_id' => 1, 'modalidade_id' => 1, 'created_at' => $agora, 'updated_at' => $agora],
            ['professor_id' => 1, 'modalidade_id' => 2, 'created_at' => $agora, 'updated_at' => $agora],
            ['professor_id' => 2, 'modalidade_id' => 2, 'created_at' => $agora, 'updated_at' => $agora],
            ['professor_id' => 3, 'modalidade_id' => 3, 'created_at' => $agora, 'updated_at' => $agora],
            ['professor_id' => 3, 'modalidade_id' => 4, 'created_at' => $agora, 'updated_at' => $agora],
        ]);
    }
}
