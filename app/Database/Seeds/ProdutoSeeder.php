<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Catálogo da loja de demonstração: roupas, equipamentos, suplementos e bebidas (RF26).
 *
 * O estoque é o saldo de hoje. Três produtos (caneleira, bandagem e água) estão abaixo do mínimo
 * de propósito, para o alerta de reposição aparecer na demonstração.
 */
class ProdutoSeeder extends Seeder
{
    /**
     * Cada linha: nome, categoria, custo, preço de venda, estoque atual, estoque mínimo, situação.
     * A ordem é usada pelo VendaSeeder (peso de popularidade por posição).
     */
    public const CATALOGO = [
        ['Camiseta CT',               'VESTUARIO',   28.00,  59.90,  45,  8, 'ATIVO'],
        ['Regata de Treino',          'VESTUARIO',   22.00,  49.90,  30,  6, 'ATIVO'],
        ['Bermuda de Treino',         'VESTUARIO',   35.00,  79.90,  25,  6, 'ATIVO'],
        ['Kimono de Jiu-Jitsu',       'VESTUARIO',  120.00, 249.90,  14,  3, 'ATIVO'],
        ['Faixa de Graduação',        'VESTUARIO',   15.00,  39.90,  20,  5, 'ATIVO'],
        ['Luvas de Boxe',             'EQUIPAMENTO', 90.00, 189.90,  12,  4, 'ATIVO'],
        ['Protetor Bucal',            'EQUIPAMENTO',  8.00,  24.90,  40, 10, 'ATIVO'],
        ['Caneleira',                 'EQUIPAMENTO', 45.00,  99.90,   3,  4, 'ATIVO'],
        ['Bandagem para Mãos',        'EQUIPAMENTO',  6.00,  19.90,   8, 10, 'ATIVO'],
        ['Whey Protein 1 kg',         'SUPLEMENTO',  95.00, 169.90,  12,  4, 'ATIVO'],
        ['Creatina 300 g',            'SUPLEMENTO',  55.00, 109.90,  10,  4, 'ATIVO'],
        ['Barra de Proteína',         'SUPLEMENTO',   3.50,   8.90,  80, 20, 'ATIVO'],
        ['Água Mineral 500 ml',       'BEBIDA',       1.20,   4.00,  25, 30, 'ATIVO'],
        ['Isotônico 500 ml',          'BEBIDA',       3.50,   8.50,  70, 20, 'ATIVO'],
        ['Garrafa Squeeze CT',        'OUTROS',      15.00,  34.90,  25,  6, 'ATIVO'],
        ['Toalha CT',                 'OUTROS',      18.00,  39.90,  18,  5, 'ATIVO'],
        ['Camiseta Edição Antiga',    'VESTUARIO',   25.00,  39.90,   6,  0, 'INATIVO'],
    ];

    public function run(): void
    {
        $agora  = date('Y-m-d H:i:s');
        $linhas = [];

        foreach (self::CATALOGO as [$nome, $categoria, $custo, $venda, $estoque, $minimo, $situacao]) {
            $linhas[] = [
                'nome'           => $nome,
                'categoria'      => $categoria,
                'preco_custo'    => $custo,
                'preco_venda'    => $venda,
                'estoque_atual'  => $estoque,
                'estoque_minimo' => $minimo,
                'situacao'       => $situacao,
                'created_at'     => $agora,
                'updated_at'     => $agora,
            ];
        }

        $this->db->table('produtos')->insertBatch($linhas);
    }
}
