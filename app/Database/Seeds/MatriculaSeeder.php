<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Matrículas da demonstração (RF07, RF08, RF09, RN01, RN02, RN04, RN10).
 *
 * Distribuição pensada para a apresentação:
 * - "Jiu-jitsu Adulto Noite" LOTADA (20 de 20) e "Futsal Misto Sábado" QUASE lotada (22 de 25),
 *   para mostrar o bloqueio de vagas;
 * - "Judô Adulto" SUSPENSA: mantém as matrículas ativas (RN10);
 * - cada aluno ativo fica em 1 ou 2 turmas, e todo menor tem responsável (cadastrado no AlunoSeeder);
 * - há matrículas TRANSFERIDAS (troca de turma, RF09) e CANCELADAS;
 * - os 12 últimos alunos fazem a turma iniciante, que o FrequenciaSeeder encerra.
 */
class MatriculaSeeder extends Seeder
{
    /** Índices (na ordem de cadastro) dos adultos que só passaram por turma cancelada ou iniciante. */
    private const SO_CANCELADOS = [17, 18];

    public function run(): void
    {
        mt_srand(DadosDemonstracao::SEMENTE + 1);

        $alunos = $this->db->table('alunos')->select('id, data_matricula')->orderBy('id')->get()->getResultArray();
        $turmas = array_column($this->db->table('turmas')->select('id, nome')->get()->getResultArray(), 'id', 'nome');

        $contagem = array_fill(0, count($alunos), 0);   // turmas ativas de cada aluno (no máximo 2)
        $membros  = [];                                 // turma => índices dos alunos matriculados

        $preencher = function (string $turma, int $alvo, array $candidatosDaTurma) use (&$membros, &$contagem): void {
            $membros[$turma] ??= [];
            $disponiveis = array_values(array_filter(
                $candidatosDaTurma,
                static fn (int $i): bool => $contagem[$i] < 2 && ! in_array($i, $membros[$turma], true),
            ));
            shuffle($disponiveis);
            // Quem tem menos turmas entra primeiro: espalha os alunos em vez de concentrá-los.
            usort($disponiveis, static fn (int $a, int $b): int => $contagem[$a] <=> $contagem[$b]);

            foreach (array_slice($disponiveis, 0, $alvo) as $i) {
                $membros[$turma][] = $i;
                $contagem[$i]++;
            }
        };

        $adultos = array_values(array_diff(range(AlunoSeeder::TOTAL_MENORES, 57), self::SO_CANCELADOS));

        // Adultos (os índices 58 e 59 só fazem a turma iniciante).
        $preencher(DadosDemonstracao::TURMA_JJ_ADULTO, 20, $adultos);
        $preencher(DadosDemonstracao::TURMA_FUTSAL_MISTO, 3, [9, 10, 11]);
        $preencher(DadosDemonstracao::TURMA_FUTSAL_MISTO, 19, $adultos);
        $preencher(DadosDemonstracao::TURMA_VOLEI, 9, $adultos);
        $preencher(DadosDemonstracao::TURMA_MT_NOITE, 7, $adultos);
        $preencher(DadosDemonstracao::TURMA_FUTSAL_NOITE, 7, $adultos);
        $preencher(DadosDemonstracao::TURMA_BOXE_ADULTO, 6, $adultos);
        $preencher(DadosDemonstracao::TURMA_JUDO_ADULTO, 6, $adultos);
        $preencher(DadosDemonstracao::TURMA_MT_MANHA, 5, $adultos);
        $preencher(DadosDemonstracao::TURMA_BASQUETE, 2, $adultos);

        // Menores: turmas infantis por faixa etária.
        $preencher(DadosDemonstracao::TURMA_JJ_KIDS, 5, range(0, 8));
        $preencher(DadosDemonstracao::TURMA_JUDO_INFANTIL, 5, range(0, 8));
        $preencher(DadosDemonstracao::TURMA_BOXE_KIDS, 4, range(3, 11));
        $preencher(DadosDemonstracao::TURMA_BASQUETE, 5, range(5, 11));

        // Quem ficou sem nenhuma turma entra em uma turma com vaga: nenhum aluno ativo fica sem matrícula.
        foreach ($alunos as $i => $aluno) {
            if ($contagem[$i] > 0 || in_array($i, self::SO_CANCELADOS, true) || $i >= 58) {
                continue;
            }
            $turma = $i < AlunoSeeder::TOTAL_MENORES
                ? ($i <= 8 ? DadosDemonstracao::TURMA_JJ_KIDS : DadosDemonstracao::TURMA_BASQUETE)
                : DadosDemonstracao::TURMA_VOLEI;
            $membros[$turma][] = $i;
            $contagem[$i]++;
        }

        // Turma iniciante: os 12 últimos alunos, matriculados desde cedo (encerrada no FrequenciaSeeder).
        $membros[DadosDemonstracao::TURMA_JJ_INICIANTE] = range(48, 59);

        $hoje   = strtotime('today');
        $ontem  = strtotime('-1 day', $hoje);
        $dataDe = static fn (int $indice) => strtotime($alunos[$indice]['data_matricula']);
        $somar  = static fn (int $timestamp, int $dias): int => strtotime("+{$dias} days", $timestamp);

        // Data de cada matrícula ativa: a primeira coincide com o cadastro; as demais vêm depois.
        $datas = [];
        $ordem = [];
        foreach ($membros as $turma => $indices) {
            foreach ($indices as $i) {
                $ordem[$i] = ($ordem[$i] ?? 0) + 1;
                $concluinte = $i >= 48;
                $datas[$turma][$i] = match (true) {
                    $turma === DadosDemonstracao::TURMA_JJ_INICIANTE => $dataDe($i),
                    $concluinte => min($hoje, $somar($dataDe($i), mt_rand(40, 100))),
                    $ordem[$i] === 1 => $dataDe($i),
                    default => min($hoje, $somar($dataDe($i), mt_rand(10, 60))),
                };
            }
        }

        $linhas = [];
        $adicionar = function (int $i, string $turma, string $situacao, int $inicio, ?int $fim = null) use (&$linhas, $alunos, $turmas): void {
            $agora    = date('Y-m-d H:i:s');
            $linhas[] = [
                'aluno_id'            => $alunos[$i]['id'],
                'turma_id'            => $turmas[$turma],
                'data_matricula'      => date('Y-m-d', $inicio),
                'situacao'            => $situacao,
                'data_encerramento'   => $fim === null ? null : date('Y-m-d', $fim),
                'certificado_emitido' => 0,
                'created_at'          => $agora,
                'updated_at'          => $agora,
            ];
        };

        // Troca de turma (RF09): três alunos da turma noturna de muay thai vieram da turma da manhã.
        $transferidos = array_slice(array_values(array_filter(
            $membros[DadosDemonstracao::TURMA_MT_NOITE],
            fn (int $i): bool => $dataDe($i) <= strtotime('-45 days', $hoje) && $i < 48,
        )), 0, 3);
        foreach ($transferidos as $i) {
            $fim = $somar($dataDe($i), 15);
            $adicionar($i, DadosDemonstracao::TURMA_MT_MANHA, 'TRANSFERIDA', $dataDe($i), $fim);
            $datas[DadosDemonstracao::TURMA_MT_NOITE][$i] = $fim;
        }

        // Matrículas ATIVAS e FINALIZÁVEIS (a da turma iniciante é finalizada pelo encerramento).
        foreach ($membros as $turma => $indices) {
            foreach ($indices as $i) {
                $adicionar($i, $turma, 'ATIVA', $datas[$turma][$i]);
            }
        }

        // Cancelamentos: dois alunos que desistiram (hoje inativos) e dois que saíram só do vôlei.
        foreach (self::SO_CANCELADOS as $i) {
            $adicionar($i, DadosDemonstracao::TURMA_MT_MANHA, 'CANCELADA', $dataDe($i), min($ontem, $somar($dataDe($i), mt_rand(20, 60))));
        }
        $desistentesDoVolei = array_slice(array_values(array_filter(
            $membros[DadosDemonstracao::TURMA_BOXE_ADULTO],
            fn (int $i): bool => $dataDe($i) <= strtotime('-60 days', $hoje) && $i < 48
                && ! in_array($i, $membros[DadosDemonstracao::TURMA_VOLEI], true),
        )), 0, 2);
        foreach ($desistentesDoVolei as $i) {
            $adicionar($i, DadosDemonstracao::TURMA_VOLEI, 'CANCELADA', $dataDe($i), min($ontem, $somar($dataDe($i), mt_rand(15, 45))));
        }

        $this->db->table('matriculas')->insertBatch($linhas);
    }
}
