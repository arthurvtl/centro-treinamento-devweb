<?php

namespace App\Controllers;

use App\Models\FrequenciaModel;
use App\Models\MatriculaModel;
use App\Models\TurmaModel;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Telas de frequência: consulta da presença por turma e data e alunos abaixo de 75% (HU04: RF10, RF11).
 *
 * A presença NÃO é lançada à mão: o aluno passa por uma catraca de acesso (biometria ou cartão) que
 * registra a entrada automaticamente. Essa integração com o equipamento é assumida pelo projeto
 * e não foi implementada; o sistema apenas lê os registros e os analisa.
 */
class Frequencias extends BaseController
{
    private FrequenciaModel $frequenciaModel;
    private TurmaModel $turmaModel;

    public function __construct()
    {
        $this->frequenciaModel = new FrequenciaModel();
        $this->turmaModel      = new TurmaModel();
    }

    /**
     * Consulta de presença: escolhe turma e data e, com a turma escolhida, lista as entradas registradas pela catraca.
     */
    public function index()
    {
        $turmaId  = (int) $this->request->getGet('turma_id');
        $dataAula = (string) ($this->request->getGet('data') ?: date('Y-m-d'));
        $turma    = $turmaId > 0 ? $this->turmaModel->buscarDetalhe($turmaId) : null;

        $dados = [
            'titulo'      => 'Frequência',
            'turmas'      => array_filter(
                $this->turmaModel->buscarComOcupacao(),
                static fn (array $item): bool => $item['situacao'] !== 'ENCERRADA',
            ),
            'turma'       => $turma,
            'dataAula'    => $dataAula,
            'alunos'      => [],
            'lancamentos' => [],
        ];

        if ($turma !== null) {
            $dados['alunos']      = (new MatriculaModel())->buscarAlunosDaTurma($turmaId);
            $dados['lancamentos'] = $this->frequenciaModel->buscarLancamentosDaAula($turmaId, $dataAula);
        }

        return view('frequencias/index', $dados);
    }

    /**
     * Lista os alunos da turma com presença abaixo de 75% (RF11, RN09).
     */
    public function baixaFrequencia(int $turmaId)
    {
        $turma = $this->turmaModel->buscarDetalhe($turmaId);

        if ($turma === null) {
            throw PageNotFoundException::forPageNotFound('Turma não encontrada.');
        }

        return view('frequencias/baixa', [
            'titulo'   => 'Alunos abaixo de ' . FrequenciaModel::FREQUENCIA_MINIMA . '% de presença',
            'turma'    => $turma,
            'resumos'  => $this->frequenciaModel->resumirPresencaDaTurma($turmaId),
            'minimo'   => FrequenciaModel::FREQUENCIA_MINIMA,
        ]);
    }
}
