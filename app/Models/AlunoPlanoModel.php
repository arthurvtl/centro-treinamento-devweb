<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Vínculo entre aluno e plano, com histórico (RF13, RN08).
 *
 * Cada aluno deve ter exatamente um plano ATIVO; os anteriores ficam ENCERRADOS.
 */
class AlunoPlanoModel extends Model
{
    protected $table         = 'aluno_planos';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['aluno_id', 'plano_id', 'data_inicio', 'data_fim', 'situacao'];

    protected $validationRules = [
        'aluno_id'    => 'required|is_natural_no_zero|is_not_unique[alunos.id]',
        'plano_id'    => 'required|is_natural_no_zero|is_not_unique[planos.id]',
        'data_inicio' => 'required|valid_date[Y-m-d]',
        'situacao'    => 'permit_empty|in_list[ATIVO,ENCERRADO]',
    ];

    protected $validationMessages = [
        'aluno_id' => [
            'required'           => 'Escolha o aluno.',
            'is_natural_no_zero' => 'Escolha o aluno.',
            'is_not_unique'      => 'O aluno escolhido não existe.',
        ],
        'plano_id' => [
            'required'           => 'Escolha o plano.',
            'is_natural_no_zero' => 'Escolha o plano.',
            'is_not_unique'      => 'O plano escolhido não existe.',
        ],
        'data_inicio' => [
            'required'   => 'Informe a data de início do plano.',
            'valid_date' => 'Informe uma data de início válida.',
        ],
        'situacao' => [
            'in_list' => 'A situação deve ser ATIVO ou ENCERRADO.',
        ],
    ];

    /**
     * Busca o plano ATIVO do aluno com os dados do plano (RN08), ou null se não tiver.
     *
     * @return array<string, mixed>|null
     */
    public function buscarAtivoDoAluno(int $alunoId): ?array
    {
        return $this->select('aluno_planos.*, planos.nome AS plano_nome, planos.valor AS plano_valor, planos.periodicidade AS plano_periodicidade')
            ->join('planos', 'planos.id = aluno_planos.plano_id')
            ->where('aluno_planos.aluno_id', $alunoId)
            ->where('aluno_planos.situacao', 'ATIVO')
            ->first();
    }
}
