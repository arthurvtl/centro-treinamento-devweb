<?php

namespace App\Database\Seeds;

use App\Services\EncerramentoTurmaService;
use CodeIgniter\Database\Seeder;

/**
 * Cerca de 8 semanas de chamadas e o encerramento da turma iniciante (RF10, RF11, RF23, RN09, RN10).
 *
 * - Cada aluno tem uma "taxa de presença"; oito alunos ficam bem abaixo de 75%, para a lista de
 *   baixa frequência (RF11) ter o que mostrar.
 * - A turma suspensa só tem chamadas até 10 dias atrás (quando foi suspensa, RN10).
 * - A turma iniciante tem as chamadas das 8 semanas anteriores a 21 dias atrás e é encerrada
 *   pelo EncerramentoTurmaService real: certificado a partir de 75% e inativação de quem ficou
 *   sem outra turma ativa (RN09).
 */
class FrequenciaSeeder extends Seeder
{
    private const SEMANAS = 8;

    /** Há quantos dias a turma iniciante foi encerrada. */
    private const DIAS_DESDE_ENCERRAMENTO = 21;

    /** Há quantos dias a turma suspensa parou de ter aula. */
    private const DIAS_DESDE_SUSPENSAO = 10;

    private const DIAS_DA_SEMANA = ['SEG' => 1, 'TER' => 2, 'QUA' => 3, 'QUI' => 4, 'SEX' => 5, 'SAB' => 6, 'DOM' => 7];

    public function run(): void
    {
        mt_srand(DadosDemonstracao::SEMENTE + 2);

        $turmas = array_column($this->db->table('turmas')->get()->getResultArray(), null, 'id');
        $alunos = array_column($this->db->table('alunos')->select('id')->orderBy('id')->get()->getResultArray(), 'id');
        $matriculas = $this->db->table('matriculas')->where('situacao', 'ATIVA')->orderBy('id')->get()->getResultArray();
        $idIniciante = (int) array_search(DadosDemonstracao::TURMA_JJ_INICIANTE, array_column($turmas, 'nome', 'id'), true);

        $taxa = $this->definirTaxasDePresenca($matriculas, $turmas, $alunos, $idIniciante);

        $hoje   = strtotime('today');
        $linhas = [];

        foreach ($matriculas as $matricula) {
            $turma = $turmas[$matricula['turma_id']];
            [$primeiro, $ultimo] = $this->janelaDeAulas($turma, $hoje, (int) $turma['id'] === $idIniciante);
            $inicioDaMatricula = strtotime($matricula['data_matricula']);

            for ($dia = $primeiro; $dia <= $ultimo; $dia = strtotime('+1 day', $dia)) {
                if ($dia < $inicioDaMatricula || ! $this->temAulaNoDia($turma['dias_semana'], $dia)) {
                    continue;
                }

                $presente = (mt_rand() / mt_getrandmax()) < $taxa[$matricula['aluno_id']];
                $linhas[] = [
                    'matricula_id' => $matricula['id'],
                    'data_aula'    => date('Y-m-d', $dia),
                    'situacao'     => $presente ? 'PRESENTE' : 'FALTA',
                    'observacao'   => (! $presente && mt_rand(1, 100) <= 15) ? 'Falta justificada (atestado).' : null,
                    'created_at'   => date('Y-m-d', $dia) . ' 21:00:00',
                    'updated_at'   => date('Y-m-d', $dia) . ' 21:00:00',
                ];
            }
        }

        foreach (array_chunk($linhas, 500) as $lote) {
            $this->db->table('frequencias')->insertBatch($lote);
        }

        $this->encerrarTurmaIniciante($idIniciante, $hoje);
    }

    /**
     * Define a taxa de presença de cada aluno: alta para quase todos e baixa para oito deles (RF11).
     *
     * Na turma iniciante, o aluno de índice 58 falta muito (sem certificado) e o 59 vai bem
     * (com certificado), os dois sem outra turma: ambos ficarão inativos, por motivos diferentes.
     *
     * @param list<array<string, mixed>> $matriculas
     * @param array<int, array<string, mixed>> $turmas
     * @param list<int|string> $alunos ids ordenados pela ordem de cadastro
     * @return array<int|string, float>
     */
    private function definirTaxasDePresenca(array $matriculas, array $turmas, array $alunos, int $idIniciante): array
    {
        $taxa = [];
        foreach ($alunos as $alunoId) {
            $taxa[$alunoId] = 0.86 + (mt_rand(0, 12) / 100);
        }

        // Candidatos à baixa frequência: alunos de turmas regulares (nunca os da turma iniciante).
        $candidatos = [];
        foreach ($matriculas as $matricula) {
            if ((int) $matricula['turma_id'] !== $idIniciante) {
                $candidatos[$matricula['aluno_id']] = true;
            }
        }
        $candidatos = array_keys($candidatos);
        shuffle($candidatos);
        foreach (array_slice($candidatos, 0, 8) as $alunoId) {
            $taxa[$alunoId] = 0.42 + (mt_rand(0, 20) / 100);
        }

        $taxa[$alunos[58]] = 0.55;
        $taxa[$alunos[59]] = 0.92;

        return $taxa;
    }

    /**
     * Calcula o primeiro e o último dia com chamadas lançadas para a turma.
     *
     * @param array<string, mixed> $turma
     * @return array{int, int} timestamps do primeiro e do último dia
     */
    private function janelaDeAulas(array $turma, int $hoje, bool $iniciante): array
    {
        $ultimo = match (true) {
            $iniciante                      => strtotime('-' . self::DIAS_DESDE_ENCERRAMENTO . ' days', $hoje),
            $turma['situacao'] === 'SUSPENSA' => strtotime('-' . self::DIAS_DESDE_SUSPENSAO . ' days', $hoje),
            default                         => strtotime('-1 day', $hoje),
        };

        return [strtotime('-' . (self::SEMANAS * 7) . ' days', $ultimo), $ultimo];
    }

    /**
     * Informa se a turma tem aula no dia da semana do timestamp (SEG, QUA, SEX...).
     */
    private function temAulaNoDia(string $diasDaTurma, int $dia): bool
    {
        $numeroDoDia = (int) date('N', $dia);

        foreach (explode(',', $diasDaTurma) as $sigla) {
            if (self::DIAS_DA_SEMANA[$sigla] === $numeroDoDia) {
                return true;
            }
        }

        return false;
    }

    /**
     * Encerra a turma iniciante com o serviço real e recua as datas para "há 21 dias" (RF23, RN09).
     *
     * O serviço grava a data de hoje; o ajuste logo depois faz a turma parecer encerrada no passado,
     * coerente com as chamadas, sem repetir a regra de certificado e de inativação.
     */
    private function encerrarTurmaIniciante(int $idIniciante, int $hoje): void
    {
        $resultado = (new EncerramentoTurmaService())->encerrar($idIniciante);
        if (! $resultado['sucesso']) {
            throw new \RuntimeException('Falha ao encerrar a turma iniciante na demonstração: ' . $resultado['mensagem']);
        }

        $dataEncerramento = date('Y-m-d', strtotime('-' . self::DIAS_DESDE_ENCERRAMENTO . ' days', $hoje));
        $this->db->table('turmas')->where('id', $idIniciante)->update(['data_termino' => $dataEncerramento]);
        $this->db->table('matriculas')->where('turma_id', $idIniciante)->where('situacao', 'FINALIZADA')
            ->update(['data_encerramento' => $dataEncerramento]);

        \CodeIgniter\CLI\CLI::write('  ' . $resultado['mensagem']);
    }
}
