<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Dados das vendas da loja e de seus itens (RF27, RF28, RN13).
 */
class VendaModel extends Model
{
    /** Situações da venda, com rótulo para exibição. */
    public const SITUACOES = ['CONCLUIDA' => 'Concluída', 'CANCELADA' => 'Cancelada'];

    protected $table         = 'vendas';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['aluno_id', 'usuario_id', 'forma_pagamento', 'valor_total', 'data_venda', 'situacao'];

    /**
     * Lista as vendas, da mais recente para a mais antiga, com o nome do aluno (se houve) e de quem vendeu.
     *
     * @return list<array<string, mixed>>
     */
    public function buscarComDetalhes(int $porPagina = 10): array
    {
        return $this->select('vendas.*, alunos.nome AS aluno_nome, alunos.codigo AS aluno_codigo, usuarios.nome AS vendedor_nome')
            ->join('alunos', 'alunos.id = vendas.aluno_id', 'left')
            ->join('usuarios', 'usuarios.id = vendas.usuario_id')
            ->orderBy('vendas.data_venda', 'DESC')
            ->orderBy('vendas.id', 'DESC')
            ->paginate($porPagina);
    }

    /**
     * Busca uma venda com aluno e vendedor, ou null se não existir.
     *
     * @return array<string, mixed>|null
     */
    public function buscarDetalhe(int $vendaId): ?array
    {
        return $this->select('vendas.*, alunos.nome AS aluno_nome, alunos.codigo AS aluno_codigo, usuarios.nome AS vendedor_nome')
            ->join('alunos', 'alunos.id = vendas.aluno_id', 'left')
            ->join('usuarios', 'usuarios.id = vendas.usuario_id')
            ->where('vendas.id', $vendaId)
            ->first();
    }

    /**
     * Lista os produtos de uma venda com o subtotal de cada linha.
     *
     * @return list<array<string, mixed>>
     */
    public function buscarItens(int $vendaId): array
    {
        return $this->db->table('venda_itens')
            ->select('venda_itens.*, produtos.nome AS produto_nome, (venda_itens.quantidade * venda_itens.preco_unitario) AS subtotal', false)
            ->join('produtos', 'produtos.id = venda_itens.produto_id')
            ->where('venda_itens.venda_id', $vendaId)
            ->orderBy('produtos.nome')
            ->get()->getResultArray();
    }

    /**
     * Resume as vendas concluídas de hoje (quantidade e valor), para o painel da recepção.
     *
     * @return array{quantidade: int, total: float}
     */
    public function resumirHoje(): array
    {
        $linha = $this->db->table($this->table)
            ->select('COUNT(*) AS quantidade, COALESCE(SUM(valor_total), 0) AS total', false)
            ->where('situacao', 'CONCLUIDA')
            ->where('data_venda >=', date('Y-m-d') . ' 00:00:00')
            ->get()->getRowArray();

        return ['quantidade' => (int) $linha['quantidade'], 'total' => (float) $linha['total']];
    }
}
