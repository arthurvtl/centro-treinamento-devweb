<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Modalidades da demonstração: 4 lutas com capacidade 20 (RN01) e 3 esportes coletivos com 25 (RN02).
 */
class ModalidadeSeeder extends Seeder
{
    public function run(): void
    {
        $agora = date('Y-m-d H:i:s');

        $modalidades = [
            ['Jiu-jitsu', 'Arte marcial de solo com graduação por faixas.', 'LUTA', 20],
            ['Muay thai', 'Luta de origem tailandesa com golpes de punhos, cotovelos, joelhos e pernas.', 'LUTA', 20],
            ['Boxe', 'Luta de golpes com os punhos, com foco em condicionamento e técnica.', 'LUTA', 20],
            ['Judô', 'Arte marcial de projeções e domínio no solo.', 'LUTA', 20],
            ['Futsal', 'Esporte coletivo de quadra com cinco jogadores por equipe.', 'ESPORTE_COLETIVO', 25],
            ['Vôlei', 'Esporte coletivo de quadra, jogado em equipes de seis.', 'ESPORTE_COLETIVO', 25],
            ['Basquete', 'Esporte coletivo de quadra com cestas de três e dois pontos.', 'ESPORTE_COLETIVO', 25],
        ];

        foreach ($modalidades as [$nome, $descricao, $categoria, $capacidade]) {
            $this->db->table('modalidades')->insert([
                'nome' => $nome, 'descricao' => $descricao, 'categoria' => $categoria,
                'capacidade_padrao' => $capacidade, 'created_at' => $agora, 'updated_at' => $agora,
            ]);
        }
    }
}
