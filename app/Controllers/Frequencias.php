<?php

namespace App\Controllers;

use App\Models\FrequenciaModel;
use App\Models\MatriculaModel;
use App\Models\TurmaModel;
use App\Services\FrequenciaService;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Telas de frequência: chamada por turma e data e alunos abaixo de 75% (HU04: RF10, RF11).
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
     * Tela de chamada: escolhe turma e data e, com a turma escolhida, lista os alunos para marcar.
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
     * Grava a chamada da turma na data informada (RF10).
     */
    public function registrar()
    {
        $turmaId  = (int) $this->request->getPost('turma_id');
        $dataAula = (string) $this->request->getPost('data_aula');

        $resultado = (new FrequenciaService())->registrarChamada(
            $turmaId,
            $dataAula,
            (array) $this->request->getPost('situacao'),
            (array) $this->request->getPost('observacao'),
        );

        $redirecionamento = redirect()->to($this->urlDaChamada($turmaId, $dataAula));

        // Em caso de erro, devolve as marcações digitadas para a pessoa não precisar refazer a chamada.
        if (! $resultado['sucesso']) {
            $redirecionamento->withInput();
        }

        return $redirecionamento->with($resultado['sucesso'] ? 'sucesso' : 'erro', $resultado['mensagem']);
    }

    /**
     * Desfaz a chamada de uma turma numa data, lançada por engano (HU04).
     */
    public function excluir(int $turmaId, string $dataAula)
    {
        $resultado = (new FrequenciaService())->excluirChamada($turmaId, $dataAula);

        return redirect()->to($this->urlDaChamada($turmaId, $dataAula))
            ->with($resultado['sucesso'] ? 'sucesso' : 'erro', $resultado['mensagem']);
    }

    /**
     * Monta o endereço da tela de chamada de uma turma numa data.
     */
    private function urlDaChamada(int $turmaId, string $dataAula): string
    {
        return url_to('frequencias') . '?turma_id=' . $turmaId . '&data=' . urlencode($dataAula);
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
