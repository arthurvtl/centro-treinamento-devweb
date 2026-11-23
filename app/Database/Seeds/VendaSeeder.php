<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Cerca de seis meses de vendas da loja, com movimento maior nas semanas mais recentes (RF27, RF28, RN12, RN13).
 *
 * - Produtos baratos (água, barra, bandagem) saem mais; kimono e whey, menos.
 * - Metade das vendas é para alunos; o restante é cliente avulso.
 * - Quatro em cada cem vendas aparecem canceladas (não baixam o estoque).
 * - O estoque atual dos produtos (definido no ProdutoSeeder) já é o saldo de hoje; as vendas
 *   históricas são só histórico e não mexem nele.
 */
class VendaSeeder extends Seeder
{
    private const QUANTIDADE_DE_VENDAS = 170;
    private const DIAS_DE_HISTORICO    = 180;

    /** Peso de saída de cada produto, na mesma ordem do catálogo do ProdutoSeeder. */
    private const POPULARIDADE = [10, 7, 6, 2, 4, 3, 7, 3, 7, 4, 3, 12, 16, 12, 4, 5, 0];

    public function run(): void
    {
        mt_srand(DadosDemonstracao::SEMENTE + 7);

        $produtos = array_column($this->db->table('produtos')->orderBy('id')->get()->getResultArray(), null, 'id');
        $ordem    = array_keys($produtos);
        $alunos   = array_column($this->db->table('alunos')->select('id')->where('situacao', 'ATIVO')->orderBy('id')->get()->getResultArray(), 'id');
        $usuarios = array_column($this->db->table('usuarios')->select('id, perfil')->orderBy('id')->get()->getResultArray(), 'id', 'perfil');

        $vendas = [];
        for ($i = 0; $i < self::QUANTIDADE_DE_VENDAS; $i++) {
            $vendas[] = $this->sortearVenda($ordem, $produtos);
        }
        usort($vendas, static fn (array $a, array $b): int => strcmp($a['data'], $b['data']));

        foreach ($vendas as $venda) {
            $itens = [];
            $total = 0.0;

            foreach ($venda['itens'] as $produtoId => $quantidade) {
                $itens[$produtoId] = $quantidade;
                $total += $quantidade * (float) $produtos[$produtoId]['preco_venda'];
            }

            if ($itens === []) {
                continue;
            }

            $this->db->table('vendas')->insert([
                'aluno_id'        => $venda['com_aluno'] ? $alunos[array_rand($alunos)] : null,
                'usuario_id'      => (mt_rand(1, 100) <= 80 ? $usuarios['RECEPCIONISTA'] : $usuarios['PROPRIETARIO']),
                'forma_pagamento' => $venda['forma'],
                'valor_total'     => number_format($total, 2, '.', ''),
                'data_venda'      => $venda['data'],
                'situacao'        => $venda['cancelada'] ? 'CANCELADA' : 'CONCLUIDA',
                'created_at'      => $venda['data'],
                'updated_at'      => $venda['data'],
            ]);
            $vendaId = $this->db->insertID();

            foreach ($itens as $produtoId => $quantidade) {
                $this->db->table('venda_itens')->insert([
                    'venda_id'       => $vendaId,
                    'produto_id'     => $produtoId,
                    'quantidade'     => $quantidade,
                    'preco_unitario' => $produtos[$produtoId]['preco_venda'],
                    'custo_unitario' => $produtos[$produtoId]['preco_custo'],
                ]);
            }
        }
    }

    /**
     * Sorteia uma venda: data (mais recente é mais provável), produtos, forma de pagamento e cliente.
     *
     * @param list<int|string> $ordem ids dos produtos na ordem do catálogo
     * @param array<int|string, array<string, mixed>> $produtos
     * @return array{data: string, itens: array<int|string, int>, forma: string, com_aluno: bool, cancelada: bool}
     */
    private function sortearVenda(array $ordem, array $produtos): array
    {
        // Potência maior que 1 concentra os dias sorteados perto de hoje (a loja cresce com o tempo).
        $diasAtras = (int) floor(self::DIAS_DE_HISTORICO * ((mt_rand() / mt_getrandmax()) ** 1.5));
        $data      = date('Y-m-d', strtotime("-{$diasAtras} days")) . sprintf(' %02d:%02d:00', mt_rand(8, 21), mt_rand(0, 59));
        if ($data > date('Y-m-d H:i:s')) {
            $data = date('Y-m-d H:i:s', strtotime('-1 hour'));
        }

        $itens = [];
        for ($n = mt_rand(1, 3); $n > 0; $n--) {
            $produtoId = $this->sortearProduto($ordem);
            if ($produtoId === null) {
                continue;
            }
            $itens[$produtoId] = ($itens[$produtoId] ?? 0) + (($produtos[$produtoId]['preco_venda'] < 20) ? mt_rand(1, 4) : 1);
        }

        $sorteioPagamento = mt_rand(1, 100);

        return [
            'data'      => $data,
            'itens'     => $itens,
            'forma'     => $sorteioPagamento <= 50 ? 'PIX' : ($sorteioPagamento <= 85 ? 'CARTAO' : 'DINHEIRO'),
            'com_aluno' => mt_rand(1, 100) <= 50,
            'cancelada' => mt_rand(1, 100) <= 4,
        ];
    }

    /**
     * Escolhe um produto com probabilidade proporcional à popularidade; null se nenhum tiver peso.
     *
     * @param list<int|string> $ordem
     */
    private function sortearProduto(array $ordem): int|string|null
    {
        $soma    = array_sum(self::POPULARIDADE);
        $sorteio = mt_rand(1, $soma);

        foreach ($ordem as $posicao => $produtoId) {
            $sorteio -= self::POPULARIDADE[$posicao] ?? 0;
            if ($sorteio <= 0) {
                return $produtoId;
            }
        }

        return null;
    }
}
