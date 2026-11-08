<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Turmas de demonstração; o limite de vagas segue a capacidade da modalidade (RN01, RN02).
 */
class TurmaSeeder extends Seeder
{
    public function run(): void
    {
        $agora = date('Y-m-d H:i:s');
        $inicio = date('Y-m-01');

        $this->db->table('turmas')->insertBatch([
            ['modalidade_id' => 1, 'professor_id' => 1, 'nome' => 'Jiu-jitsu Adulto Noite', 'dias_semana' => 'SEG,QUA,SEX',
             'hora_inicio' => '19:00:00', 'hora_termino' => '20:30:00', 'limite_vagas' => 20, 'situacao' => 'ATIVA',
             'data_inicio' => $inicio, 'data_termino' => null, 'created_at' => $agora, 'updated_at' => $agora],
            ['modalidade_id' => 2, 'professor_id' => 2, 'nome' => 'Muay Thai Kids', 'dias_semana' => 'TER,QUI',
             'hora_inicio' => '17:00:00', 'hora_termino' => '18:00:00', 'limite_vagas' => 20, 'situacao' => 'ATIVA',
             'data_inicio' => $inicio, 'data_termino' => null, 'created_at' => $agora, 'updated_at' => $agora],
            ['modalidade_id' => 3, 'professor_id' => 3, 'nome' => 'Vôlei Misto', 'dias_semana' => 'SAB',
             'hora_inicio' => '09:00:00', 'hora_termino' => '11:00:00', 'limite_vagas' => 25, 'situacao' => 'ATIVA',
             'data_inicio' => $inicio, 'data_termino' => null, 'created_at' => $agora, 'updated_at' => $agora],
        ]);
    }
}
