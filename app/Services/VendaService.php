<?php

namespace App\Services;

use App\Models\AlunoModel;
use App\Models\MensalidadeModel;
use App\Models\ProdutoModel;
use App\Models\VendaModel;

/**
 * Fluxo de venda da loja: registrar com baixa de estoque e cancelar com devolução (RF27, RF28, RN12, RN13, RN14).
 *
 * Mexe em três tabelas (vendas, venda_itens e produtos), por isso fica num Service e roda numa
 * transação: ou tudo é gravado, ou nada é.
 */
class VendaService
{
    public function __construct(
        private readonly VendaModel $vendaModel = new VendaModel(),
        private readonly ProdutoModel $produtoModel = new ProdutoModel(),
        private readonly AlunoModel $alunoModel = new AlunoModel(),
    ) {
    }

    /**
     * Registra uma venda e baixa o estoque dos produtos.
     *
     * @param array<int|string, int|string> $quantidades quantidade por produto: [produto_id => quantidade]; zero ou vazio é ignorado
     * @param int|null $alunoId aluno comprador (opcional: o cliente pode ser avulso)
     * @return array{sucesso: bool, mensagem: string, venda_id?: int}
     */
    public function registrar(array $quantidades, ?int $alunoId, string $formaPagamento, int $usuarioId): array
    {
        helper('formatacao');

        // Só entram as linhas com quantidade positiva.
        $pedido = [];
        foreach ($quantidades as $produtoId => $quantidade) {
            $quantidade = (int) $quantidade;
            if ($quantidade > 0) {
                $pedido[(int) $produtoId] = $quantidade;
            }
        }

        if ($pedido === []) {
            return ['sucesso' => false, 'mensagem' => 'Informe a quantidade de pelo menos um produto.'];
        }

        if (! isset(MensalidadeModel::FORMAS_PAGAMENTO[$formaPagamento])) {
            return ['sucesso' => false, 'mensagem' => 'Escolha a forma de pagamento.'];
        }

        if ($alunoId !== null && $this->alunoModel->find($alunoId) === null) {
            return ['sucesso' => false, 'mensagem' => 'O aluno informado não existe.'];
        }

        $produtos = array_column($this->produtoModel->whereIn('id', array_keys($pedido))->findAll(), null, 'id');
        $total    = 0.0;

        foreach ($pedido as $produtoId => $quantidade) {
            $produto = $produtos[$produtoId] ?? null;

            // RN14: só produto ativo é vendido.
            if ($produto === null || $produto['situacao'] !== 'ATIVO') {
                return ['sucesso' => false, 'mensagem' => 'Um dos produtos não está disponível para venda.'];
            }

            // RN12: a venda não pode deixar o estoque negativo.
            if ($quantidade > (int) $produto['estoque_atual']) {
                return ['sucesso' => false, 'mensagem' => 'Estoque insuficiente de "' . $produto['nome'] . '": restam ' . $produto['estoque_atual'] . '.'];
            }

            $total += $quantidade * (float) $produto['preco_venda'];
        }

        $banco = db_connect();
        $banco->transStart();

        $this->vendaModel->insert([
            'aluno_id'        => $alunoId,
            'usuario_id'      => $usuarioId,
            'forma_pagamento' => $formaPagamento,
            'valor_total'     => number_format($total, 2, '.', ''),
            'data_venda'      => date('Y-m-d H:i:s'),
            'situacao'        => 'CONCLUIDA',
        ]);
        $vendaId = (int) $this->vendaModel->getInsertID();

        foreach ($pedido as $produtoId => $quantidade) {
            $produto = $produtos[$produtoId];

            // RN13: preço e custo do momento ficam gravados no item.
            $banco->table('venda_itens')->insert([
                'venda_id'       => $vendaId,
                'produto_id'     => $produtoId,
                'quantidade'     => $quantidade,
                'preco_unitario' => $produto['preco_venda'],
                'custo_unitario' => $produto['preco_custo'],
            ]);

            // Baixa condicional: só desconta se ainda houver estoque (protege contra duas vendas ao mesmo tempo).
            $banco->query(
                'UPDATE produtos SET estoque_atual = estoque_atual - ? WHERE id = ? AND estoque_atual >= ?',
                [$quantidade, $produtoId, $quantidade],
            );

            if ($banco->affectedRows() !== 1) {
                $banco->transRollback();

                return ['sucesso' => false, 'mensagem' => 'Estoque insuficiente de "' . $produto['nome'] . '". Atualize a tela e tente novamente.'];
            }
        }

        $banco->transComplete();

        if (! $banco->transStatus()) {
            return ['sucesso' => false, 'mensagem' => 'Não foi possível registrar a venda. Tente novamente.'];
        }

        return ['sucesso' => true, 'mensagem' => 'Venda registrada: ' . formatar_moeda($total) . '.', 'venda_id' => $vendaId];
    }

    /**
     * Cancela uma venda concluída e devolve os produtos ao estoque (RF28).
     *
     * @return array{sucesso: bool, mensagem: string}
     */
    public function cancelar(int $vendaId): array
    {
        $venda = $this->vendaModel->find($vendaId);

        if ($venda === null) {
            return ['sucesso' => false, 'mensagem' => 'Venda não encontrada.'];
        }

        if ($venda['situacao'] === 'CANCELADA') {
            return ['sucesso' => false, 'mensagem' => 'Esta venda já foi cancelada.'];
        }

        $banco = db_connect();
        $banco->transStart();

        foreach ($this->vendaModel->buscarItens($vendaId) as $item) {
            $banco->query(
                'UPDATE produtos SET estoque_atual = estoque_atual + ? WHERE id = ?',
                [(int) $item['quantidade'], (int) $item['produto_id']],
            );
        }

        $this->vendaModel->update($vendaId, ['situacao' => 'CANCELADA']);
        $banco->transComplete();

        return $banco->transStatus()
            ? ['sucesso' => true, 'mensagem' => 'Venda cancelada e produtos devolvidos ao estoque.']
            : ['sucesso' => false, 'mensagem' => 'Não foi possível cancelar a venda. Tente novamente.'];
    }
}
