<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Dados do responsável legal de aluno menor de idade (RF03, RN04).
 */
class ResponsavelLegalModel extends Model
{
    protected $table         = 'responsaveis_legais';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['aluno_id', 'nome', 'cpf', 'telefone', 'email', 'parentesco'];

    protected $validationRules = [
        'nome'       => 'required|min_length[3]|max_length[120]',
        'cpf'        => 'required|cpf_valido',
        'telefone'   => 'required|min_length[10]|max_length[20]',
        'email'      => 'permit_empty|valid_email|max_length[150]',
        'parentesco' => 'required|max_length[30]',
    ];

    protected $validationMessages = [
        'nome' => [
            'required'   => 'Informe o nome do responsável legal.',
            'min_length' => 'O nome do responsável deve ter pelo menos 3 caracteres.',
            'max_length' => 'O nome do responsável deve ter no máximo 120 caracteres.',
        ],
        'cpf' => [
            'required'   => 'Informe o CPF do responsável legal.',
            'cpf_valido' => 'O CPF do responsável não é válido.',
        ],
        'telefone' => [
            'required'   => 'Informe o telefone do responsável legal.',
            'min_length' => 'O telefone do responsável deve ter DDD e número.',
            'max_length' => 'O telefone do responsável deve ter no máximo 20 caracteres.',
        ],
        'email' => [
            'valid_email' => 'Informe um e-mail válido para o responsável.',
            'max_length'  => 'O e-mail do responsável deve ter no máximo 150 caracteres.',
        ],
        'parentesco' => [
            'required'   => 'Informe o parentesco do responsável (ex.: mãe, pai, tutor).',
            'max_length' => 'O parentesco deve ter no máximo 30 caracteres.',
        ],
    ];

    /**
     * Busca o responsável legal de um aluno (relação 1 para 0..1).
     *
     * @return array<string, mixed>|null
     */
    public function buscarPorAluno(int $alunoId): ?array
    {
        return $this->where('aluno_id', $alunoId)->first();
    }
}
