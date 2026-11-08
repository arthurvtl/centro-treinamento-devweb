<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Mensalidades dos últimos meses (RF14, RF15, RF24, RN07, RN11).
 *
 * - Vencimento nos dias 5, 10, 15, 20 ou 25, conforme o aluno; planos trimestral e semestral
 *   geram uma cobrança a cada 3 e 6 meses.
 * - Vencidas: quase todas PAGAS (PIX, cartão e dinheiro); em 8 alunos as últimas ficam ATRASADAS,
 *   e esses são os inadimplentes da lista (RF15, RN11).
 * - A vencer (até 25 dias à frente) ou vencendo hoje: EM_ABERTO, sem nunca estar vencida e em aberto,
 *   porque isso já seria atraso (RN07).
 */
class MensalidadeSeeder extends Seeder
{
    /** Quantos alunos ficam inadimplentes na demonstração. */
    private const INADIMPLENTES = 8;

    /** Quantas mensalidades vencidas e não pagas cada inadimplente tem, na ordem. */
    private const ATRASOS = [1, 2, 3, 1, 2, 1, 3, 2];

    /** Janela de histórico mostrada, em dias. */
    private const HISTORICO_DIAS = 190;

    public function run(): void
    {
        mt_srand(DadosDemonstracao::SEMENTE + 4);

        $hoje    = strtotime('today');
        $limite  = strtotime('+25 days', $hoje);
        $minimo  = strtotime('-' . self::HISTORICO_DIAS . ' days', $hoje);
        $vinculos = $this->db->table('aluno_planos')
            ->select('aluno_planos.id, aluno_planos.aluno_id, aluno_planos.data_inicio, aluno_planos.data_fim, aluno_planos.situacao, '
                . 'planos.valor, planos.periodicidade')
            ->join('planos', 'planos.id = aluno_planos.plano_id')
            ->orderBy('aluno_planos.id')->get()->getResultArray();

        $meses = ['MENSAL' => 1, 'TRIMESTRAL' => 3, 'SEMESTRAL' => 6];

        // Vencimentos de cada vínculo dentro da janela, sem ainda definir a situação.
        $cobrancas = [];
        foreach ($vinculos as $vinculo) {
            $dia   = [5, 10, 15, 20, 25][$vinculo['aluno_id'] % 5];
            $passo = $meses[$vinculo['periodicidade']];
            $fimDoVinculo = $vinculo['data_fim'] !== null ? strtotime($vinculo['data_fim']) : $limite;
            $inicio = strtotime($vinculo['data_inicio']);

            $vencimento = strtotime(date('Y-m-', $inicio) . sprintf('%02d', $dia));
            if ($vencimento < $inicio) {
                $vencimento = strtotime("+{$passo} months", $vencimento);
            }

            while ($vencimento <= $fimDoVinculo) {
                if ($vencimento >= $minimo) {
                    $cobrancas[$vinculo['id']][] = $vencimento;
                }
                $vencimento = strtotime("+{$passo} months", $vencimento);
            }
        }

        // Inadimplentes: vínculos ATIVOS que já têm alguma cobrança vencida, um por aluno.
        $candidatos = [];
        foreach ($vinculos as $vinculo) {
            $vencidas = array_filter($cobrancas[$vinculo['id']] ?? [], static fn (int $v): bool => $v < $hoje);
            if ($vinculo['situacao'] === 'ATIVO' && $vencidas !== []) {
                $candidatos[$vinculo['id']] = count($vencidas);
            }
        }
        $ids = array_keys($candidatos);
        shuffle($ids);
        $atrasos = array_combine(array_slice($ids, 0, self::INADIMPLENTES), array_slice(self::ATRASOS, 0, min(self::INADIMPLENTES, count($ids))));

        $linhas = [];
        foreach ($vinculos as $vinculo) {
            $datas   = $cobrancas[$vinculo['id']] ?? [];
            $vencidas = array_values(array_filter($datas, static fn (int $v): bool => $v < $hoje));
            $primeiraAtrasada = count($vencidas) - min($atrasos[$vinculo['id']] ?? 0, count($vencidas));

            foreach ($datas as $vencimento) {
                $posicaoVencida = array_search($vencimento, $vencidas, true);
                $emAtraso       = $posicaoVencida !== false && $posicaoVencida >= $primeiraAtrasada && isset($atrasos[$vinculo['id']]);

                $linhas[] = $this->montarLinha($vinculo, $vencimento, $hoje, $emAtraso);
            }
        }

        foreach (array_chunk($linhas, 500) as $lote) {
            $this->db->table('mensalidades')->insertBatch($lote);
        }
    }

    /**
     * Monta uma mensalidade: a vencer vira EM_ABERTO, vencida vira PAGA ou, para inadimplentes, ATRASADA.
     *
     * @param array<string, mixed> $vinculo
     * @return array<string, mixed>
     */
    private function montarLinha(array $vinculo, int $vencimento, int $hoje, bool $emAtraso): array
    {
        $situacao = $vencimento >= $hoje ? 'EM_ABERTO' : ($emAtraso ? 'ATRASADO' : 'PAGO');
        $pagamento = null;
        $forma     = null;

        if ($situacao === 'PAGO') {
            $pagamento = date('Y-m-d', min($hoje, strtotime('+' . mt_rand(0, 5) . ' days', $vencimento)));
            $sorteio   = mt_rand(1, 100);
            $forma     = $sorteio <= 55 ? 'PIX' : ($sorteio <= 80 ? 'CARTAO' : 'DINHEIRO');
        }

        return [
            'aluno_plano_id'  => $vinculo['id'],
            'valor'           => $vinculo['valor'],
            'vencimento'      => date('Y-m-d', $vencimento),
            'data_pagamento'  => $pagamento,
            'forma_pagamento' => $forma,
            'situacao'        => $situacao,
            'created_at'      => date('Y-m-d', strtotime('-10 days', $vencimento)) . ' 08:00:00',
            'updated_at'      => ($pagamento ?? date('Y-m-d', strtotime('-10 days', $vencimento))) . ' 08:00:00',
        ];
    }
}
