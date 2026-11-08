<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Dados e regras de planos de pagamento (RF12).
 */
class PlanoModel extends Model
{
    /** Periodicidades aceitas, com o rótulo mostrado ao usuário. */
    public const PERIODICIDADES = [
        'MENSAL'     => 'Mensal',
        'TRIMESTRAL' => 'Trimestral',
        'SEMESTRAL'  => 'Semestral',
    ];

    protected $table         = 'planos';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['nome', 'valor', 'periodicidade', 'beneficios', 'situacao'];

    protected $validationRules = [
        'nome'          => 'required|min_length[3]|max_length[80]',
        'valor'         => 'required|decimal|greater_than[0]|less_than[100000]',
        'periodicidade' => 'required|in_list[MENSAL,TRIMESTRAL,SEMESTRAL]',
        'beneficios'    => 'permit_empty|max_length[1000]',
        'situacao'      => 'permit_empty|in_list[ATIVO,INATIVO]',
    ];

    protected $validationMessages = [
        'nome' => [
            'required'   => 'Informe o nome do plano.',
            'min_length' => 'O nome deve ter pelo menos 3 caracteres.',
            'max_length' => 'O nome deve ter no máximo 80 caracteres.',
        ],
        'valor' => [
            'required'     => 'Informe o valor do plano.',
            'decimal'      => 'Informe o valor em reais, por exemplo 120,00.',
            'greater_than' => 'O valor do plano deve ser maior que zero.',
            'less_than'    => 'O valor do plano está acima do permitido.',
        ],
        'periodicidade' => [
            'required' => 'Escolha a periodicidade do plano.',
            'in_list'  => 'Escolha uma periodicidade válida.',
        ],
        'beneficios' => [
            'max_length' => 'Os benefícios devem ter no máximo 1000 caracteres.',
        ],
        'situacao' => [
            'in_list' => 'A situação deve ser ATIVO ou INATIVO.',
        ],
    ];

    /**
     * Converte o valor digitado em reais (aceita vírgula, ex.: 120,50) para o formato decimal do banco.
     *
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    public function normalizarValor(array $dados): array
    {
        if (isset($dados['valor'])) {
            $dados['valor'] = str_replace(',', '.', str_replace('.', '', trim((string) $dados['valor'])));
        }

        return $dados;
    }

    /**
     * Informa se o plano já foi vinculado a algum aluno, o que impede excluí-lo.
     */
    public function possuiAlunos(int $planoId): bool
    {
        return $this->db->table('aluno_planos')->where('plano_id', $planoId)->countAllResults() > 0;
    }
}
