<?php

namespace App\Controllers;

use App\Models\AlunoModel;
use App\Models\MatriculaModel;
use App\Models\TurmaModel;
use App\Services\MatriculaService;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Telas de matrícula (HU03: RF07, RF08, RF09).
 */
class Matriculas extends BaseController
{
    private MatriculaModel $matriculaModel;
    private MatriculaService $matriculaService;

    public function __construct()
    {
        $this->matriculaModel   = new MatriculaModel();
        $this->matriculaService = new MatriculaService($this->matriculaModel);
    }

    /**
     * Lista as matrículas ativas, com busca por nome ou código do aluno.
     */
    public function index()
    {
        $busca = trim((string) $this->request->getGet('busca'));

        return view('matriculas/index', [
            'titulo'     => 'Matrículas',
            'matriculas' => $this->matriculaModel->buscarAtivas($busca),
            'pager'      => $this->matriculaModel->pager,
            'busca'      => $busca,
        ]);
    }

    /**
     * Exibe o formulário de nova matrícula, já com a turma escolhida quando vier da tela da turma.
     */
    public function nova()
    {
        return view('matriculas/formulario', [
            'titulo'          => 'Nova matrícula',
            'alunos'          => (new AlunoModel())->select('id, codigo, nome')->where('situacao', 'ATIVO')->orderBy('nome')->findAll(),
            'turmas'          => (new TurmaModel())->buscarComOcupacao('ATIVA'),
            'turmaPreferida'  => (int) $this->request->getGet('turma_id'),
        ]);
    }

    /**
     * Matricula o aluno na turma, aplicando vagas, situação, duplicidade e menor de idade.
     */
    public function criar()
    {
        $resultado = $this->matriculaService->matricular(
            (int) $this->request->getPost('aluno_id'),
            (int) $this->request->getPost('turma_id'),
        );

        if (! $resultado['sucesso']) {
            return redirect()->back()->withInput()->with('erro', $resultado['mensagem']);
        }

        return redirect()->to(url_to('matriculas'))->with('sucesso', $resultado['mensagem']);
    }

    /**
     * Exibe o formulário de troca de turma de uma matrícula ativa (RF09).
     */
    public function trocar(int $id)
    {
        $matricula = $this->buscarAtivaOuFalhar($id);

        return view('matriculas/trocar', [
            'titulo'    => 'Trocar de turma',
            'matricula' => $matricula,
            'aluno'     => (new AlunoModel())->select('id, codigo, nome')->find($matricula['aluno_id']),
            'turmas'    => array_filter(
                (new TurmaModel())->buscarComOcupacao('ATIVA'),
                static fn (array $turma): bool => (int) $turma['id'] !== (int) $matricula['turma_id'],
            ),
        ]);
    }

    /**
     * Efetiva a troca de turma (RF09).
     */
    public function efetivarTroca(int $id)
    {
        $this->buscarAtivaOuFalhar($id);
        $resultado = $this->matriculaService->trocarTurma($id, (int) $this->request->getPost('turma_id'));

        if (! $resultado['sucesso']) {
            return redirect()->back()->withInput()->with('erro', $resultado['mensagem']);
        }

        return redirect()->to(url_to('matriculas'))->with('sucesso', $resultado['mensagem']);
    }

    /**
     * Cancela a matrícula, liberando a vaga.
     */
    public function cancelar(int $id)
    {
        $resultado = $this->matriculaService->cancelar($id);

        return redirect()->to(url_to('matriculas'))->with($resultado['sucesso'] ? 'sucesso' : 'erro', $resultado['mensagem']);
    }

    /**
     * Busca a matrícula ATIVA ou responde 404.
     *
     * @return array<string, mixed>
     */
    private function buscarAtivaOuFalhar(int $id): array
    {
        $matricula = $this->matriculaModel->find($id);

        if ($matricula === null || $matricula['situacao'] !== 'ATIVA') {
            throw PageNotFoundException::forPageNotFound('Matrícula ativa não encontrada.');
        }

        return $matricula;
    }
}
