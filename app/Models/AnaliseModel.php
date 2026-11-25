<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Consultas de apoio à tela de análise do proprietário (HU09, RF29).
 *
 * Só lê dados de outras tabelas: nada é gravado aqui. Os métodos devolvem números agregados
 * (somas, contagens, médias); montar percentuais e variações é papel do AnaliseService.
 * Os textos SQL usam só aspas simples, pois o MySQL do Aiven roda em modo ANSI_QUOTES.
 */
class AnaliseModel extends Model
{
    protected $table      = 'vendas';
    protected $returnType = 'array';

    /**
     * Soma o que entrou de mensalidades por mês de pagamento, a partir de uma data.
     *
     * @return array<string, float> [AAAA-MM => total]
     */
    public function mensalidadesRecebidasPorMes(string $desde): array
    {
        $linhas = $this->db->query(
            "SELECT DATE_FORMAT(data_pagamento, '%Y-%m') AS mes, SUM(valor) AS total
               FROM mensalidades
              WHERE situacao = 'PAGO' AND data_pagamento >= ?
              GROUP BY mes",
            [$desde],
        )->getResultArray();

        return array_map('floatval', array_column($linhas, 'total', 'mes'));
    }

    /**
     * Resume a loja por mês: receita, lucro bruto (preço menos custo) e quantidade de vendas concluídas.
     *
     * @return array<string, array{receita: float, lucro: float, vendas: int}> [AAAA-MM => resumo]
     */
    public function lojaPorMes(string $desde): array
    {
        $linhas = $this->db->query(
            "SELECT DATE_FORMAT(v.data_venda, '%Y-%m') AS mes,
                    SUM(i.quantidade * i.preco_unitario) AS receita,
                    SUM(i.quantidade * (i.preco_unitario - i.custo_unitario)) AS lucro,
                    COUNT(DISTINCT v.id) AS vendas
               FROM vendas v
               JOIN venda_itens i ON i.venda_id = v.id
              WHERE v.situacao = 'CONCLUIDA' AND v.data_venda >= ?
              GROUP BY mes",
            [$desde . ' 00:00:00'],
        )->getResultArray();

        $resumo = [];
        foreach ($linhas as $linha) {
            $resumo[$linha['mes']] = ['receita' => (float) $linha['receita'], 'lucro' => (float) $linha['lucro'], 'vendas' => (int) $linha['vendas']];
        }

        return $resumo;
    }

    /**
     * Soma a receita da loja por categoria de produto, a partir de uma data.
     *
     * @return list<array{categoria: string, receita: float, quantidade: int}>
     */
    public function lojaPorCategoria(string $desde): array
    {
        $linhas = $this->db->query(
            "SELECT p.categoria, SUM(i.quantidade * i.preco_unitario) AS receita, SUM(i.quantidade) AS quantidade
               FROM venda_itens i
               JOIN vendas v ON v.id = i.venda_id
               JOIN produtos p ON p.id = i.produto_id
              WHERE v.situacao = 'CONCLUIDA' AND v.data_venda >= ?
              GROUP BY p.categoria
              ORDER BY receita DESC",
            [$desde . ' 00:00:00'],
        )->getResultArray();

        return array_map(static fn (array $l): array => ['categoria' => $l['categoria'], 'receita' => (float) $l['receita'], 'quantidade' => (int) $l['quantidade']], $linhas);
    }

    /**
     * Lista os produtos que mais venderam em receita, com quantidade e lucro, a partir de uma data.
     *
     * @return list<array{nome: string, quantidade: int, receita: float, lucro: float}>
     */
    public function produtosMaisVendidos(string $desde, int $limite = 5): array
    {
        $linhas = $this->db->query(
            "SELECT p.nome, SUM(i.quantidade) AS quantidade,
                    SUM(i.quantidade * i.preco_unitario) AS receita,
                    SUM(i.quantidade * (i.preco_unitario - i.custo_unitario)) AS lucro
               FROM venda_itens i
               JOIN vendas v ON v.id = i.venda_id
               JOIN produtos p ON p.id = i.produto_id
              WHERE v.situacao = 'CONCLUIDA' AND v.data_venda >= ?
              GROUP BY p.id, p.nome
              ORDER BY receita DESC
              LIMIT " . max(1, $limite),
            [$desde . ' 00:00:00'],
        )->getResultArray();

        return array_map(static fn (array $l): array => [
            'nome' => $l['nome'], 'quantidade' => (int) $l['quantidade'], 'receita' => (float) $l['receita'], 'lucro' => (float) $l['lucro'],
        ], $linhas);
    }

    /**
     * Soma a receita de mensalidades pagas por plano, a partir de uma data.
     *
     * @return list<array{plano: string, alunos: int, receita: float}>
     */
    public function receitaPorPlano(string $desde): array
    {
        $linhas = $this->db->query(
            "SELECT pl.nome AS plano, COUNT(DISTINCT ap.aluno_id) AS alunos, SUM(m.valor) AS receita
               FROM mensalidades m
               JOIN aluno_planos ap ON ap.id = m.aluno_plano_id
               JOIN planos pl ON pl.id = ap.plano_id
              WHERE m.situacao = 'PAGO' AND m.data_pagamento >= ?
              GROUP BY pl.id, pl.nome
              ORDER BY receita DESC",
            [$desde],
        )->getResultArray();

        return array_map(static fn (array $l): array => ['plano' => $l['plano'], 'alunos' => (int) $l['alunos'], 'receita' => (float) $l['receita']], $linhas);
    }

    /**
     * Totais para a taxa de inadimplência: tudo que já venceu e o quanto disso está atrasado.
     *
     * @return array{vencido: float, atrasado: float, alunos_inadimplentes: int}
     */
    public function totaisDeInadimplencia(): array
    {
        (new MensalidadeModel())->atualizarAtrasadas();

        $linha = $this->db->query(
            "SELECT COALESCE(SUM(m.valor), 0) AS vencido,
                    COALESCE(SUM(CASE WHEN m.situacao = 'ATRASADO' THEN m.valor ELSE 0 END), 0) AS atrasado,
                    COUNT(DISTINCT CASE WHEN m.situacao = 'ATRASADO' THEN ap.aluno_id END) AS alunos
               FROM mensalidades m
               JOIN aluno_planos ap ON ap.id = m.aluno_plano_id
              WHERE m.vencimento < CURDATE()",
        )->getRowArray();

        return ['vencido' => (float) $linha['vencido'], 'atrasado' => (float) $linha['atrasado'], 'alunos_inadimplentes' => (int) $linha['alunos']];
    }

    /**
     * Distribui o valor em atraso por faixa de dias vencidos (1-30, 31-60, mais de 60).
     *
     * @return list<array{faixa: string, quantidade: int, valor: float}>
     */
    public function atrasoPorFaixa(): array
    {
        $linha = $this->db->query(
            "SELECT
                SUM(CASE WHEN DATEDIFF(CURDATE(), vencimento) <= 30 THEN 1 ELSE 0 END) AS q1,
                COALESCE(SUM(CASE WHEN DATEDIFF(CURDATE(), vencimento) <= 30 THEN valor ELSE 0 END), 0) AS v1,
                SUM(CASE WHEN DATEDIFF(CURDATE(), vencimento) BETWEEN 31 AND 60 THEN 1 ELSE 0 END) AS q2,
                COALESCE(SUM(CASE WHEN DATEDIFF(CURDATE(), vencimento) BETWEEN 31 AND 60 THEN valor ELSE 0 END), 0) AS v2,
                SUM(CASE WHEN DATEDIFF(CURDATE(), vencimento) > 60 THEN 1 ELSE 0 END) AS q3,
                COALESCE(SUM(CASE WHEN DATEDIFF(CURDATE(), vencimento) > 60 THEN valor ELSE 0 END), 0) AS v3
               FROM mensalidades
              WHERE situacao = 'ATRASADO'",
        )->getRowArray();

        return [
            ['faixa' => 'Até 30 dias',      'quantidade' => (int) $linha['q1'], 'valor' => (float) $linha['v1']],
            ['faixa' => '31 a 60 dias',     'quantidade' => (int) $linha['q2'], 'valor' => (float) $linha['v2']],
            ['faixa' => 'Mais de 60 dias',  'quantidade' => (int) $linha['q3'], 'valor' => (float) $linha['v3']],
        ];
    }

    /**
     * Conta alunos com matrícula ativa por modalidade.
     *
     * @return list<array{modalidade: string, alunos: int}>
     */
    public function alunosPorModalidade(): array
    {
        $linhas = $this->db->query(
            "SELECT mo.nome AS modalidade, COUNT(DISTINCT ma.aluno_id) AS alunos
               FROM matriculas ma
               JOIN turmas t ON t.id = ma.turma_id
               JOIN modalidades mo ON mo.id = t.modalidade_id
              WHERE ma.situacao = 'ATIVA'
              GROUP BY mo.id, mo.nome
              ORDER BY alunos DESC",
        )->getResultArray();

        return array_map(static fn (array $l): array => ['modalidade' => $l['modalidade'], 'alunos' => (int) $l['alunos']], $linhas);
    }

    /**
     * Calcula a presença média por modalidade (registros da catraca) a partir de uma data.
     *
     * @return list<array{modalidade: string, aulas: int, percentual: float}>
     */
    public function frequenciaPorModalidade(string $desde): array
    {
        $linhas = $this->db->query(
            "SELECT mo.nome AS modalidade, COUNT(f.id) AS aulas,
                    SUM(CASE WHEN f.situacao = 'PRESENTE' THEN 1 ELSE 0 END) AS presencas
               FROM frequencias f
               JOIN matriculas ma ON ma.id = f.matricula_id
               JOIN turmas t ON t.id = ma.turma_id
               JOIN modalidades mo ON mo.id = t.modalidade_id
              WHERE f.data_aula >= ?
              GROUP BY mo.id, mo.nome
              ORDER BY mo.nome",
            [$desde],
        )->getResultArray();

        return array_map(static fn (array $l): array => [
            'modalidade' => $l['modalidade'],
            'aulas'      => (int) $l['aulas'],
            'percentual' => (int) $l['aulas'] > 0 ? round(((int) $l['presencas'] / (int) $l['aulas']) * 100, 1) : 0.0,
        ], $linhas);
    }

    /**
     * Conta os alunos com matrícula ativa e presença abaixo do mínimo (75%), considerando todas as aulas registradas.
     */
    public function contarAlunosEmRisco(): int
    {
        $linha = $this->db->query(
            "SELECT COUNT(*) AS total FROM (
                SELECT ma.aluno_id
                  FROM matriculas ma
                  JOIN frequencias f ON f.matricula_id = ma.id
                 WHERE ma.situacao = 'ATIVA'
                 GROUP BY ma.aluno_id
                HAVING (SUM(CASE WHEN f.situacao = 'PRESENTE' THEN 1 ELSE 0 END) / COUNT(f.id)) * 100 < ?
             ) risco",
            [FrequenciaModel::FREQUENCIA_MINIMA],
        )->getRowArray();

        return (int) $linha['total'];
    }

    /**
     * Conta matrículas novas por mês, a partir de uma data.
     *
     * @return array<string, int> [AAAA-MM => quantidade]
     */
    public function matriculasNovasPorMes(string $desde): array
    {
        $linhas = $this->db->query(
            "SELECT DATE_FORMAT(data_matricula, '%Y-%m') AS mes, COUNT(*) AS total
               FROM matriculas
              WHERE data_matricula >= ?
              GROUP BY mes",
            [$desde],
        )->getResultArray();

        return array_map('intval', array_column($linhas, 'total', 'mes'));
    }

    /**
     * Conta matrículas canceladas desde uma data (evasão).
     */
    public function contarCancelamentosDesde(string $desde): int
    {
        return (int) $this->db->table('matriculas')
            ->where('situacao', 'CANCELADA')
            ->where('data_encerramento >=', $desde)
            ->countAllResults();
    }
}
