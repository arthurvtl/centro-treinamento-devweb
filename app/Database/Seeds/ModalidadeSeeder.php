<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Modalidades iniciais: lutas com capacidade 20 (RN01) e coletivos com 25 (RN02).
 */
class ModalidadeSeeder extends Seeder
{
    public function run(): void
    {
        $agora = date('Y-m-d H:i:s');

        $this->db->table('modalidades')->insertBatch([
            ['nome' => 'Jiu-jitsu', 'descricao' => 'Arte marcial de solo com graduação por faixas.', 'categoria' => 'LUTA',
             'capacidade_padrao' => 20, 'created_at' => $agora, 'updated_at' => $agora],
            ['nome' => 'Muay thai', 'descricao' => 'Luta de origem tailandesa.', 'categoria' => 'LUTA',
             'capacidade_padrao' => 20, 'created_at' => $agora, 'updated_at' => $agora],
            ['nome' => 'Vôlei', 'descricao' => 'Esporte coletivo de quadra.', 'categoria' => 'ESPORTE_COLETIVO',
             'capacidade_padrao' => 25, 'created_at' => $agora, 'updated_at' => $agora],
            ['nome' => 'Futsal', 'descricao' => 'Esporte coletivo de quadra.', 'categoria' => 'ESPORTE_COLETIVO',
             'capacidade_padrao' => 25, 'created_at' => $agora, 'updated_at' => $agora],
        ]);
    }
}
