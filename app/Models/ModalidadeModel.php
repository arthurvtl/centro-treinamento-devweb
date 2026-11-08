<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Dados e regras de modalidades (RF05, RN01, RN02).
 */
class ModalidadeModel extends Model
{
    /** Rótulos das categorias para exibição. */
    public const CATEGORIAS = [
        'LUTA'             => 'Luta',
        'ESPORTE_COLETIVO' => 'Esporte coletivo',
        'INDIVIDUAL'       => 'Individual',
    ];

    protected $table         = 'modalidades';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['nome', 'descricao', 'categoria', 'capacidade_padrao'];

    protected $validationRules = [
        'id'                => 'permit_empty|is_natural_no_zero', // exigida pelo CI4 para o placeholder {id}
        'nome'              => 'required|min_length[3]|max_length[80]|is_unique[modalidades.nome,id,{id}]',
        'descricao'         => 'permit_empty|max_length[1000]',
        'categoria'         => 'required|in_list[LUTA,ESPORTE_COLETIVO,INDIVIDUAL]',
        'capacidade_padrao' => 'required|is_natural_no_zero|capacidade_da_categoria[categoria]',
    ];

    protected $validationMessages = [
        'nome' => [
            'required'   => 'Informe o nome da modalidade.',
            'min_length' => 'O nome deve ter pelo menos 3 caracteres.',
            'max_length' => 'O nome deve ter no máximo 80 caracteres.',
            'is_unique'  => 'Já existe uma modalidade com este nome.',
        ],
        'descricao' => [
            'max_length' => 'A descrição deve ter no máximo 1000 caracteres.',
        ],
        'categoria' => [
            'required' => 'Escolha a categoria da modalidade.',
            'in_list'  => 'Escolha uma categoria válida.',
        ],
        'capacidade_padrao' => [
            'required'           => 'Informe a capacidade padrão de alunos por turma.',
            'is_natural_no_zero' => 'A capacidade padrão deve ser um número maior que zero.',
        ],
    ];

    /**
     * Lista todas as modalidades em ordem alfabética, para telas e seletores.
     *
     * @return list<array<string, mixed>>
     */
    public function buscarTodasOrdenadas(): array
    {
        return $this->orderBy('nome')->findAll();
    }

    /**
     * Informa se a modalidade já tem turmas, o que impede excluí-la.
     */
    public function possuiTurmas(int $modalidadeId): bool
    {
        return $this->db->table('turmas')->where('modalidade_id', $modalidadeId)->countAllResults() > 0;
    }
}
