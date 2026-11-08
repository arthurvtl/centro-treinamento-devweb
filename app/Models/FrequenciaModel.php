<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Dados de frequência por aula (RF10, RF11, RN09, HU04).
 */
class FrequenciaModel extends Model
{
    /** Percentual mínimo de presença para a turma ser considerada em dia e para o certificado (RN09). */
    public const FREQUENCIA_MINIMA = 75;

    protected $table         = 'frequencias';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['matricula_id', 'data_aula', 'situacao', 'observacao'];

    protected $validationRules = [
        'matricula_id' => 'required|is_natural_no_zero|is_not_unique[matriculas.id]',
        'data_aula'    => 'required|valid_date[Y-m-d]|data_nao_futura',
        'situacao'     => 'required|in_list[PRESENTE,FALTA]',
        'observacao'   => 'permit_empty|max_length[255]',
    ];

    protected $validationMessages = [
        'matricula_id' => [
            'required'           => 'Matrícula não informada.',
            'is_natural_no_zero' => 'Matrícula inválida.',
            'is_not_unique'      => 'A matrícula informada não existe.',
        ],
        'data_aula' => [
            'required'        => 'Informe a data da aula.',
            'valid_date'      => 'Informe uma data de aula válida.',
            'data_nao_futura' => 'Não é possível lançar frequência de uma data futura.',
        ],
        'situacao' => [
            'required' => 'Marque presença ou falta para todos os alunos.',
            'in_list'  => 'A situação deve ser PRESENTE ou FALTA.',
        ],
        'observacao' => [
            'max_length' => 'A observação deve ter no máximo 255 caracteres.',
        ],
    ];

    /**
     * Devolve os lançamentos já feitos de uma turma numa data, indexados pelo id da matrícula.
     *
     * Serve para a tela de chamada mostrar o que já foi registrado e permitir corrigir.
     *
     * @return array<int, array<string, mixed>>
     */
    public function buscarLancamentosDaAula(int $turmaId, string $dataAula): array
    {
        $linhas = $this->select('frequencias.*')
            ->join('matriculas', 'matriculas.id = frequencias.matricula_id')
            ->where('matriculas.turma_id', $turmaId)
            ->where('frequencias.data_aula', $dataAula)
            ->findAll();

        return array_column($linhas, null, 'matricula_id');
    }

    /**
     * Calcula presença de cada aluno com matrícula ATIVA na turma (RF11, RN09).
     *
     * O percentual considera só as aulas já lançadas para o aluno; quem ainda não tem
     * nenhum lançamento fica com `percentual` nulo e não entra como "abaixo do mínimo".
     *
     * @return list<array<string, mixed>> matricula_id, aluno_id, aluno_nome, aluno_codigo, presencas, aulas, percentual
     */
    public function resumirPresencaDaTurma(int $turmaId): array
    {
        $linhas = $this->db->table('matriculas')
            ->select('matriculas.id AS matricula_id, alunos.id AS aluno_id, alunos.nome AS aluno_nome, alunos.codigo AS aluno_codigo, '
                . 'COUNT(frequencias.id) AS aulas, COALESCE(SUM(frequencias.situacao = "PRESENTE"), 0) AS presencas', false)
            ->join('alunos', 'alunos.id = matriculas.aluno_id')
            ->join('frequencias', 'frequencias.matricula_id = matriculas.id', 'left')
            ->where('matriculas.turma_id', $turmaId)
            ->where('matriculas.situacao', 'ATIVA')
            ->groupBy('matriculas.id, alunos.id, alunos.nome, alunos.codigo')
            ->orderBy('alunos.nome')
            ->get()->getResultArray();

        foreach ($linhas as &$linha) {
            $linha['percentual'] = (int) $linha['aulas'] > 0
                ? round(((int) $linha['presencas'] / (int) $linha['aulas']) * 100, 1)
                : null;
        }

        return $linhas;
    }

    /**
     * Apaga todos os lançamentos de uma turma numa data e devolve quantos foram apagados.
     *
     * Serve para desfazer uma chamada lançada por engano (HU04); depois dá para lançar de novo.
     */
    public function excluirLancamentosDaAula(int $turmaId, string $dataAula): int
    {
        $ids = array_column($this->buscarLancamentosDaAula($turmaId, $dataAula), 'id');

        if ($ids === []) {
            return 0;
        }

        $this->whereIn('id', $ids)->delete();

        return count($ids);
    }
}
