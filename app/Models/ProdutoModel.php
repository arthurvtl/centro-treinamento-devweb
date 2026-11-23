<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Dados e regras dos produtos da loja do centro (RF26, RN12, RN14).
 */
class ProdutoModel extends Model
{
    /** Categorias aceitas, com o rótulo mostrado ao usuário. */
    public const CATEGORIAS = [
        'VESTUARIO'   => 'Vestuário',
        'EQUIPAMENTO' => 'Equipamentos',
        'SUPLEMENTO'  => 'Suplementos',
        'BEBIDA'      => 'Bebidas',
        'OUTROS'      => 'Outros',
    ];

    protected $table         = 'produtos';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['nome', 'categoria', 'preco_custo', 'preco_venda', 'estoque_atual', 'estoque_minimo', 'situacao'];

    protected $validationRules = [
        'nome'           => 'required|min_length[3]|max_length[80]',
        'categoria'      => 'required|in_list[VESTUARIO,EQUIPAMENTO,SUPLEMENTO,BEBIDA,OUTROS]',
        'preco_custo'    => 'required|decimal|greater_than_equal_to[0]|less_than[100000]',
        'preco_venda'    => 'required|decimal|greater_than[0]|less_than[100000]',
        'estoque_atual'  => 'required|is_natural|less_than[100000]',
        'estoque_minimo' => 'required|is_natural|less_than[100000]',
        'situacao'       => 'permit_empty|in_list[ATIVO,INATIVO]',
    ];

    protected $validationMessages = [
        'nome' => [
            'required'   => 'Informe o nome do produto.',
            'min_length' => 'O nome deve ter pelo menos 3 caracteres.',
            'max_length' => 'O nome deve ter no máximo 80 caracteres.',
        ],
        'categoria' => [
            'required' => 'Escolha a categoria do produto.',
            'in_list'  => 'Escolha uma categoria válida.',
        ],
        'preco_custo' => [
            'required'              => 'Informe o preço de custo.',
            'decimal'               => 'Informe o custo em reais, por exemplo 25,00.',
            'greater_than_equal_to' => 'O custo não pode ser negativo.',
            'less_than'             => 'O custo está acima do permitido.',
        ],
        'preco_venda' => [
            'required'     => 'Informe o preço de venda.',
            'decimal'      => 'Informe o preço em reais, por exemplo 49,90.',
            'greater_than' => 'O preço de venda deve ser maior que zero.',
            'less_than'    => 'O preço de venda está acima do permitido.',
        ],
        'estoque_atual' => [
            'required'   => 'Informe a quantidade em estoque.',
            'is_natural' => 'O estoque deve ser um número inteiro, zero ou maior.',
            'less_than'  => 'O estoque está acima do permitido.',
        ],
        'estoque_minimo' => [
            'required'   => 'Informe o estoque mínimo.',
            'is_natural' => 'O estoque mínimo deve ser um número inteiro, zero ou maior.',
            'less_than'  => 'O estoque mínimo está acima do permitido.',
        ],
        'situacao' => [
            'in_list' => 'A situação deve ser ATIVO ou INATIVO.',
        ],
    ];

    /**
     * Converte os preços digitados em reais (aceita vírgula, ex.: 49,90) para o formato decimal do banco.
     *
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    public function normalizarPrecos(array $dados): array
    {
        foreach (['preco_custo', 'preco_venda'] as $campo) {
            if (isset($dados[$campo])) {
                $dados[$campo] = str_replace(',', '.', str_replace('.', '', trim((string) $dados[$campo])));
            }
        }

        return $dados;
    }

    /**
     * Lista todos os produtos em ordem alfabética (tela de gestão).
     *
     * @return list<array<string, mixed>>
     */
    public function buscarTodosOrdenados(): array
    {
        return $this->orderBy('nome')->findAll();
    }

    /**
     * Lista os produtos ativos que ainda têm estoque, os únicos que podem ser vendidos (RN12, RN14).
     *
     * @return list<array<string, mixed>>
     */
    public function buscarVendaveis(): array
    {
        return $this->where('situacao', 'ATIVO')->where('estoque_atual >', 0)->orderBy('categoria')->orderBy('nome')->findAll();
    }

    /**
     * Lista os produtos ativos com estoque igual ou abaixo do mínimo, para o alerta de reposição.
     *
     * @return list<array<string, mixed>>
     */
    public function buscarEstoqueBaixo(): array
    {
        return $this->where('situacao', 'ATIVO')->where('estoque_atual <= estoque_minimo', null, false)->orderBy('estoque_atual')->findAll();
    }

    /**
     * Informa se o produto já foi vendido alguma vez, o que impede excluí-lo (o histórico depende dele).
     */
    public function possuiVendas(int $produtoId): bool
    {
        return $this->db->table('venda_itens')->where('produto_id', $produtoId)->countAllResults() > 0;
    }
}
