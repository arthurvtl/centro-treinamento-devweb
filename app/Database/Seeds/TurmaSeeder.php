<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * 13 turmas em dias e horários variados (manhã, tarde e noite), infantis e adultas.
 *
 * Cobre os cenários da apresentação: uma turma SUSPENSA (RN10), uma turma lotada e outra quase
 * lotada (RF08) e uma turma que será encerrada pelo FrequenciaSeeder, com certificados (RN09).
 * O limite de vagas respeita a capacidade da modalidade (RN01, RN02) e o professor é sempre
 * habilitado na modalidade (RN03).
 */
class TurmaSeeder extends Seeder
{
    public function run(): void
    {
        $agora    = date('Y-m-d H:i:s');
        $inicio   = DadosDemonstracao::diasAtras(380);
        $modalidades = array_column($this->db->table('modalidades')->select('id, nome')->get()->getResultArray(), 'id', 'nome');
        $professores = array_column($this->db->table('professores')->select('id, nome')->get()->getResultArray(), 'id', 'nome');

        // [nome, modalidade, professor, dias, início, término, limite de vagas, situação]
        $turmas = [
            [DadosDemonstracao::TURMA_JJ_ADULTO, 'Jiu-jitsu', 'Marcão Gomes', 'SEG,QUA,SEX', '19:00', '20:30', 20, 'ATIVA'],
            [DadosDemonstracao::TURMA_JJ_KIDS, 'Jiu-jitsu', 'Patrícia Nunes', 'TER,QUI', '17:00', '18:00', 15, 'ATIVA'],
            [DadosDemonstracao::TURMA_JJ_INICIANTE, 'Jiu-jitsu', 'Patrícia Nunes', 'SEG,QUA', '08:00', '09:00', 20, 'ATIVA'],
            [DadosDemonstracao::TURMA_MT_MANHA, 'Muay thai', 'Rafael Teixeira', 'SEG,QUA,SEX', '07:00', '08:00', 15, 'ATIVA'],
            [DadosDemonstracao::TURMA_MT_NOITE, 'Muay thai', 'Vanclei Moura', 'TER,QUI', '20:00', '21:00', 20, 'ATIVA'],
            [DadosDemonstracao::TURMA_BOXE_ADULTO, 'Boxe', 'Vanclei Moura', 'SEG,QUA', '18:30', '19:30', 18, 'ATIVA'],
            [DadosDemonstracao::TURMA_BOXE_KIDS, 'Boxe', 'Vanclei Moura', 'SAB', '09:00', '10:00', 12, 'ATIVA'],
            [DadosDemonstracao::TURMA_JUDO_INFANTIL, 'Judô', 'Juliana Prado', 'TER,QUI', '16:00', '17:00', 15, 'ATIVA'],
            [DadosDemonstracao::TURMA_JUDO_ADULTO, 'Judô', 'Marcão Gomes', 'TER,QUI', '19:00', '20:00', 20, 'SUSPENSA'],
            [DadosDemonstracao::TURMA_FUTSAL_MISTO, 'Futsal', 'Carlos Eduardo Souza', 'SAB', '08:00', '10:00', 25, 'ATIVA'],
            [DadosDemonstracao::TURMA_VOLEI, 'Vôlei', 'Renata Lima', 'SEG,QUA', '20:00', '21:30', 25, 'ATIVA'],
            [DadosDemonstracao::TURMA_BASQUETE, 'Basquete', 'Thiago Barbosa', 'TER,QUI', '17:30', '19:00', 25, 'ATIVA'],
            [DadosDemonstracao::TURMA_FUTSAL_NOITE, 'Futsal', 'Thiago Barbosa', 'SEX', '20:00', '22:00', 25, 'ATIVA'],
        ];

        foreach ($turmas as [$nome, $modalidade, $professor, $dias, $horaInicio, $horaTermino, $limite, $situacao]) {
            $this->db->table('turmas')->insert([
                'modalidade_id' => $modalidades[$modalidade], 'professor_id' => $professores[$professor],
                'nome' => $nome, 'dias_semana' => $dias, 'hora_inicio' => $horaInicio . ':00', 'hora_termino' => $horaTermino . ':00',
                'limite_vagas' => $limite, 'situacao' => $situacao, 'data_inicio' => $inicio, 'data_termino' => null,
                'created_at' => $agora, 'updated_at' => $agora,
            ]);
        }
    }
}
