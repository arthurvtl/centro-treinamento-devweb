<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Dados do vínculo entre aluno e turma (RF07, RF08, RF09, RN10).
 */
class MatriculaModel extends Model
{
    protected $table         = 'matriculas';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'aluno_id', 'turma_id', 'data_matricula', 'situacao', 'data_encerramento', 'certificado_emitido',
    ];

    protected $validationRules = [
        'aluno_id'       => 'required|is_natural_no_zero|is_not_unique[alunos.id]',
        'turma_id'       => 'required|is_natural_no_zero|is_not_unique[turmas.id]',
        'data_matricula' => 'required|valid_date[Y-m-d]',
        'situacao'       => 'permit_empty|in_list[ATIVA,CANCELADA,TRANSFERIDA,FINALIZADA]',
    ];

    protected $validationMessages = [
        'aluno_id' => [
            'required'           => 'Escolha o aluno.',
            'is_natural_no_zero' => 'Escolha o aluno.',
            'is_not_unique'      => 'O aluno escolhido não existe.',
        ],
        'turma_id' => [
            'required'           => 'Escolha a turma.',
            'is_natural_no_zero' => 'Escolha a turma.',
            'is_not_unique'      => 'A turma escolhida não existe.',
        ],
        'data_matricula' => [
            'required'   => 'Informe a data da matrícula.',
            'valid_date' => 'Informe uma data de matrícula válida.',
        ],
        'situacao' => [
            'in_list' => 'Situação de matrícula inválida.',
        ],
    ];

    /**
     * Informa se o aluno já tem matrícula ATIVA na turma, para evitar duplicidade (HU03).
     */
    public function possuiMatriculaAtiva(int $alunoId, int $turmaId): bool
    {
        return $this->where('aluno_id', $alunoId)->where('turma_id', $turmaId)->where('situacao', 'ATIVA')->countAllResults() > 0;
    }

    /**
     * Lista matrículas ATIVAS com aluno, turma e modalidade, com busca por nome ou código do aluno (HU03).
     *
     * @return list<array<string, mixed>>
     */
    public function buscarAtivas(?string $termo, int $porPagina = 10): array
    {
        $this->select('matriculas.*, alunos.nome AS aluno_nome, alunos.codigo AS aluno_codigo, '
            . 'turmas.nome AS turma_nome, turmas.situacao AS turma_situacao, modalidades.nome AS modalidade_nome')
            ->join('alunos', 'alunos.id = matriculas.aluno_id')
            ->join('turmas', 'turmas.id = matriculas.turma_id')
            ->join('modalidades', 'modalidades.id = turmas.modalidade_id')
            ->where('matriculas.situacao', 'ATIVA')
            ->orderBy('alunos.nome');

        if ($termo !== null && $termo !== '') {
            $this->groupStart()->like('alunos.nome', $termo)->orLike('alunos.codigo', $termo)->groupEnd();
        }

        return $this->paginate($porPagina);
    }

    /**
     * Lista os alunos com matrícula ATIVA em uma turma (tela da turma).
     *
     * @return list<array<string, mixed>>
     */
    public function buscarAlunosDaTurma(int $turmaId): array
    {
        return $this->select('matriculas.id, matriculas.data_matricula, alunos.id AS aluno_id, alunos.nome AS aluno_nome, alunos.codigo AS aluno_codigo')
            ->join('alunos', 'alunos.id = matriculas.aluno_id')
            ->where('matriculas.turma_id', $turmaId)
            ->where('matriculas.situacao', 'ATIVA')
            ->orderBy('alunos.nome')
            ->findAll();
    }

    /**
     * Lista as matrículas FINALIZADAS de uma turma encerrada, com a indicação de certificado (RF23, RN09).
     *
     * @return list<array<string, mixed>>
     */
    public function buscarFinalizadasDaTurma(int $turmaId): array
    {
        return $this->select('matriculas.id, matriculas.certificado_emitido, alunos.nome AS aluno_nome, alunos.codigo AS aluno_codigo')
            ->join('alunos', 'alunos.id = matriculas.aluno_id')
            ->where('matriculas.turma_id', $turmaId)
            ->where('matriculas.situacao', 'FINALIZADA')
            ->orderBy('alunos.nome')
            ->findAll();
    }

    /**
     * Busca os dados do certificado de uma matrícula, ou null se ela não tem certificado emitido (RN09).
     *
     * @return array<string, mixed>|null
     */
    public function buscarCertificado(int $matriculaId): ?array
    {
        return $this->select('matriculas.*, alunos.nome AS aluno_nome, alunos.codigo AS aluno_codigo, '
                . 'turmas.nome AS turma_nome, modalidades.nome AS modalidade_nome, professores.nome AS professor_nome')
            ->join('alunos', 'alunos.id = matriculas.aluno_id')
            ->join('turmas', 'turmas.id = matriculas.turma_id')
            ->join('modalidades', 'modalidades.id = turmas.modalidade_id')
            ->join('professores', 'professores.id = turmas.professor_id')
            ->where('matriculas.id', $matriculaId)
            ->where('matriculas.certificado_emitido', 1)
            ->first();
    }

    /**
     * Busca uma matrícula com os dados do aluno, da turma e da modalidade (tela de detalhe).
     *
     * @return array<string, mixed>|null
     */
    public function buscarDetalhe(int $matriculaId): ?array
    {
        return $this->select('matriculas.*, alunos.nome AS aluno_nome, alunos.codigo AS aluno_codigo, '
                . 'turmas.nome AS turma_nome, turmas.situacao AS turma_situacao, modalidades.nome AS modalidade_nome')
            ->join('alunos', 'alunos.id = matriculas.aluno_id')
            ->join('turmas', 'turmas.id = matriculas.turma_id')
            ->join('modalidades', 'modalidades.id = turmas.modalidade_id')
            ->where('matriculas.id', $matriculaId)
            ->first();
    }

    /**
     * Informa se a matrícula já tem frequência lançada, o que impede excluí-la (HU04).
     */
    public function possuiFrequencias(int $matriculaId): bool
    {
        return $this->db->table('frequencias')->where('matricula_id', $matriculaId)->countAllResults() > 0;
    }
}
