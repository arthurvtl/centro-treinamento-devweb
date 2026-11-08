<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Planos, vínculos aluno-plano (um ativo por aluno, RN08) e mensalidades de demonstração
 * nos três estados: paga, em aberto e atrasada (RN07).
 */
class PlanoSeeder extends Seeder
{
    public function run(): void
    {
        $agora = date('Y-m-d H:i:s');

        $this->db->table('planos')->insertBatch([
            ['nome' => 'Mensal Básico', 'valor' => 120.00, 'periodicidade' => 'MENSAL', 'beneficios' => 'Uma modalidade.',
             'situacao' => 'ATIVO', 'created_at' => $agora, 'updated_at' => $agora],
            ['nome' => 'Trimestral', 'valor' => 330.00, 'periodicidade' => 'TRIMESTRAL', 'beneficios' => 'Até duas modalidades.',
             'situacao' => 'ATIVO', 'created_at' => $agora, 'updated_at' => $agora],
        ]);

        // Aluno 1: plano 1; aluno 2: plano 1; aluno 3: plano 2; aluno 4: plano 1.
        foreach ([[1, 1], [2, 1], [3, 2], [4, 1]] as [$alunoId, $planoId]) {
            $this->db->table('aluno_planos')->insert([
                'aluno_id' => $alunoId, 'plano_id' => $planoId, 'data_inicio' => date('Y-m-01', strtotime('-2 months')),
                'data_fim' => null, 'situacao' => 'ATIVO', 'created_at' => $agora, 'updated_at' => $agora,
            ]);
        }

        $mensalidades = [
            // [aluno_plano_id, valor, vencimento, pagamento, forma, situacao]
            [1, 120.00, date('Y-m-d', strtotime('-20 days')), date('Y-m-d', strtotime('-18 days')), 'PIX', 'PAGO'],
            [1, 120.00, date('Y-m-d', strtotime('+10 days')), null, null, 'EM_ABERTO'],
            [2, 120.00, date('Y-m-d', strtotime('-15 days')), null, null, 'ATRASADO'],
            [3, 330.00, date('Y-m-d', strtotime('+5 days')), null, null, 'EM_ABERTO'],
            [4, 120.00, date('Y-m-d', strtotime('-5 days')), date('Y-m-d', strtotime('-4 days')), 'DINHEIRO', 'PAGO'],
        ];

        foreach ($mensalidades as [$vinculoId, $valor, $vencimento, $pagamento, $forma, $situacao]) {
            $this->db->table('mensalidades')->insert([
                'aluno_plano_id' => $vinculoId, 'valor' => $valor, 'vencimento' => $vencimento,
                'data_pagamento' => $pagamento, 'forma_pagamento' => $forma, 'situacao' => $situacao,
                'created_at' => $agora, 'updated_at' => $agora,
            ]);
        }
    }
}
