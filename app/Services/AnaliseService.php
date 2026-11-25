<?php

namespace App\Services;

use App\Models\AlunoModel;
use App\Models\AnaliseModel;
use App\Models\MatriculaModel;
use App\Models\ProdutoModel;
use App\Models\TurmaModel;

/**
 * Monta a visão analítica do proprietário: receita, inadimplência, ocupação, retenção, frequência e loja (HU09, RF29, RN05).
 *
 * Reúne os números do AnaliseModel e calcula o que depende de combinação entre eles
 * (variação mensal, percentuais, margem). A View só desenha o resultado.
 */
class AnaliseService
{
    /** Quantos meses (incluindo o atual) aparecem nos gráficos de evolução. */
    private const MESES_NA_SERIE = 6;

    /** Janela, em dias, usada para frequência e ranking da loja. */
    private const JANELA_EM_DIAS = 90;

    /** Janela, em dias, usada para medir a evasão (cancelamentos são raros, então olha-se um ano). */
    private const JANELA_EVASAO_EM_DIAS = 365;

    public function __construct(
        private readonly AnaliseModel $analiseModel = new AnaliseModel(),
        private readonly AlunoModel $alunoModel = new AlunoModel(),
        private readonly MatriculaModel $matriculaModel = new MatriculaModel(),
        private readonly TurmaModel $turmaModel = new TurmaModel(),
        private readonly ProdutoModel $produtoModel = new ProdutoModel(),
    ) {
    }

    /**
     * Devolve todos os blocos da tela de análise.
     *
     * @return array<string, mixed>
     */
    public function montar(): array
    {
        $inicioDaSerie = date('Y-m-01', strtotime('-' . (self::MESES_NA_SERIE - 1) . ' months'));
        $inicioDaJanela = date('Y-m-d', strtotime('-' . self::JANELA_EM_DIAS . ' days'));

        $serie    = $this->montarSerieMensal($inicioDaSerie);
        $mesAtual = end($serie);
        $anterior = $serie[count($serie) - 2] ?? ['total' => 0.0];

        $ocupacao = $this->calcularOcupacao();

        $inadimplencia = $this->analiseModel->totaisDeInadimplencia();
        $taxaInadimplencia = $inadimplencia['vencido'] > 0 ? round(($inadimplencia['atrasado'] / $inadimplencia['vencido']) * 100, 1) : 0.0;

        $ativos      = (int) $this->matriculaModel->contarAtivas();
        $cancelados  = $this->analiseModel->contarCancelamentosDesde(date('Y-m-d', strtotime('-' . self::JANELA_EVASAO_EM_DIAS . ' days')));
        $taxaEvasao  = ($ativos + $cancelados) > 0 ? round(($cancelados / ($ativos + $cancelados)) * 100, 1) : 0.0;
        $alunosAtivos = $this->alunoModel->contarPorSituacao()['ativos'];

        $lojaJanela = $this->analiseModel->produtosMaisVendidos($inicioDaJanela, 5);

        return [
            'indicadores' => [
                'receita_mes'      => $mesAtual['total'],
                'variacao_receita' => $anterior['total'] > 0 ? round((($mesAtual['total'] - $anterior['total']) / $anterior['total']) * 100, 1) : null,
                'ticket_aluno'     => $alunosAtivos > 0 ? $mesAtual['mensalidades'] / $alunosAtivos : 0.0,
                'ocupacao_media'   => $ocupacao['media'],
                'inadimplencia'    => $taxaInadimplencia,
                'alunos_atrasados' => $inadimplencia['alunos_inadimplentes'],
                'evasao'           => $taxaEvasao,
                'cancelamentos'    => $cancelados,
                'em_risco'         => $this->analiseModel->contarAlunosEmRisco(),
                'alunos_ativos'    => $alunosAtivos,
                'ticket_loja'      => $mesAtual['vendas'] > 0 ? $mesAtual['loja'] / $mesAtual['vendas'] : 0.0,
                'margem_loja'      => $this->calcularMargemDaLoja($serie),
                'estoque_baixo'    => count($this->produtoModel->buscarEstoqueBaixo()),
            ],
            'serie'            => $serie,
            'turmas'           => $ocupacao['turmas'],
            'alunos_modalidade' => $this->analiseModel->alunosPorModalidade(),
            'frequencia'       => $this->analiseModel->frequenciaPorModalidade($inicioDaJanela),
            'atraso_faixas'    => $this->analiseModel->atrasoPorFaixa(),
            'receita_planos'   => $this->analiseModel->receitaPorPlano($inicioDaSerie),
            'loja_categorias'  => $this->analiseModel->lojaPorCategoria($inicioDaJanela),
            'loja_top'         => $lojaJanela,
            'janela_dias'      => self::JANELA_EM_DIAS,
            'janela_evasao_dias' => self::JANELA_EVASAO_EM_DIAS,
            'meses_na_serie'   => self::MESES_NA_SERIE,
        ];
    }

    /**
     * Junta, mês a mês, mensalidades recebidas, receita e lucro da loja e matrículas novas.
     *
     * @return list<array{mes: string, rotulo: string, mensalidades: float, loja: float, lucro_loja: float, vendas: int, total: float, novas: int}>
     */
    private function montarSerieMensal(string $inicio): array
    {
        $mensalidades = $this->analiseModel->mensalidadesRecebidasPorMes($inicio);
        $loja         = $this->analiseModel->lojaPorMes($inicio);
        $novas        = $this->analiseModel->matriculasNovasPorMes($inicio);

        $serie = [];
        for ($i = self::MESES_NA_SERIE - 1; $i >= 0; $i--) {
            $mes     = date('Y-m', strtotime(date('Y-m-01') . " -{$i} months"));
            $recebido = $mensalidades[$mes] ?? 0.0;
            $receitaLoja = $loja[$mes]['receita'] ?? 0.0;

            $serie[] = [
                'mes'          => $mes,
                'rotulo'       => substr($mes, 5, 2) . '/' . substr($mes, 0, 4),
                'mensalidades' => $recebido,
                'loja'         => $receitaLoja,
                'lucro_loja'   => $loja[$mes]['lucro'] ?? 0.0,
                'vendas'       => $loja[$mes]['vendas'] ?? 0,
                'total'        => $recebido + $receitaLoja,
                'novas'        => $novas[$mes] ?? 0,
            ];
        }

        return $serie;
    }

    /**
     * Calcula a ocupação de cada turma ativa e a média geral (vagas preenchidas sobre vagas oferecidas).
     *
     * @return array{media: float, turmas: list<array{nome: string, modalidade: string, ocupadas: int, vagas: int, percentual: float}>}
     */
    private function calcularOcupacao(): array
    {
        $turmas   = [];
        $ocupadas = 0;
        $vagas    = 0;

        foreach ($this->turmaModel->buscarComOcupacao('ATIVA') as $turma) {
            $ocupadasDaTurma = (int) $turma['ocupadas'];
            $vagasDaTurma    = (int) $turma['limite_vagas'];
            $ocupadas       += $ocupadasDaTurma;
            $vagas          += $vagasDaTurma;

            $turmas[] = [
                'nome'       => $turma['nome'],
                'modalidade' => $turma['modalidade_nome'],
                'ocupadas'   => $ocupadasDaTurma,
                'vagas'      => $vagasDaTurma,
                'percentual' => $vagasDaTurma > 0 ? round(($ocupadasDaTurma / $vagasDaTurma) * 100, 1) : 0.0,
            ];
        }

        usort($turmas, static fn (array $a, array $b): int => $b['percentual'] <=> $a['percentual']);

        return ['media' => $vagas > 0 ? round(($ocupadas / $vagas) * 100, 1) : 0.0, 'turmas' => $turmas];
    }

    /**
     * Calcula a margem bruta da loja (lucro sobre receita) somando todos os meses da série.
     *
     * @param list<array<string, mixed>> $serie
     */
    private function calcularMargemDaLoja(array $serie): float
    {
        $receita = array_sum(array_column($serie, 'loja'));
        $lucro   = array_sum(array_column($serie, 'lucro_loja'));

        return $receita > 0 ? round(($lucro / $receita) * 100, 1) : 0.0;
    }
}
