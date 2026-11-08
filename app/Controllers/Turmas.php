<?php

namespace App\Controllers;

use App\Models\MatriculaModel;
use App\Models\ModalidadeModel;
use App\Models\ProfessorModel;
use App\Models\TurmaModel;
use App\Services\EncerramentoTurmaService;
use App\Services\TurmaService;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Telas de turmas (HU03: RF06, RF22). Consulta para os dois perfis; alterações só do proprietário (rotas).
 */
class Turmas extends BaseController
{
    private TurmaModel $turmaModel;
    private TurmaService $turmaService;

    public function __construct()
    {
        $this->turmaModel   = new TurmaModel();
        $this->turmaService = new TurmaService($this->turmaModel);
    }

    /**
     * Lista as turmas com professor, modalidade e vagas ocupadas.
     */
    public function index()
    {
        return view('turmas/index', [
            'titulo' => 'Turmas',
            'turmas' => $this->turmaModel->buscarComOcupacao(),
        ]);
    }

    /**
     * Mostra a turma e os alunos matriculados nela.
     */
    public function ver(int $id)
    {
        $turma = $this->turmaModel->buscarDetalhe($id);

        if ($turma === null) {
            throw PageNotFoundException::forPageNotFound('Turma não encontrada.');
        }

        return view('turmas/ver', [
            'titulo' => $turma['nome'],
            'turma'  => $turma,
            'alunos' => (new MatriculaModel())->buscarAlunosDaTurma($id),
            'finalizadas' => (new MatriculaModel())->buscarFinalizadasDaTurma($id),
        ]);
    }

    /**
     * Exibe o formulário de nova turma.
     */
    public function novo()
    {
        return $this->formulario('Nova turma', ['data_inicio' => date('Y-m-d')], url_to('turmas_criar'));
    }

    /**
     * Grava uma nova turma (RF06), validando professor habilitado (RN03) e limite de vagas.
     */
    public function criar()
    {
        $resultado = $this->turmaService->cadastrar($this->request->getPost());

        if (! $resultado['sucesso']) {
            return redirect()->back()->withInput()->with('erros', $resultado['erros']);
        }

        return redirect()->to(url_to('turmas_ver', $resultado['id']))->with('sucesso', 'Turma cadastrada com sucesso.');
    }

    /**
     * Exibe o formulário de edição preenchido.
     */
    public function editar(int $id)
    {
        return $this->formulario('Editar turma', $this->buscarOuFalhar($id), url_to('turmas_atualizar', $id));
    }

    /**
     * Salva as alterações da turma.
     */
    public function atualizar(int $id)
    {
        $this->buscarOuFalhar($id);
        $resultado = $this->turmaService->atualizar($id, $this->request->getPost());

        if (! $resultado['sucesso']) {
            return redirect()->back()->withInput()->with('erros', $resultado['erros']);
        }

        return redirect()->to(url_to('turmas_ver', $id))->with('sucesso', 'Turma atualizada com sucesso.');
    }

    /**
     * Suspende a turma (RF22, RN10).
     */
    public function suspender(int $id)
    {
        return $this->responderSituacao($id, $this->turmaService->suspender($id));
    }

    /**
     * Reativa a turma suspensa (RF22).
     */
    public function reativar(int $id)
    {
        return $this->responderSituacao($id, $this->turmaService->reativar($id));
    }

    /**
     * Encerra a turma: finaliza matrículas, emite certificados e inativa alunos sem outra turma (RF23, RN09).
     */
    public function encerrar(int $id)
    {
        $resultado = (new EncerramentoTurmaService())->encerrar($id);

        return $this->responderSituacao($id, $resultado);
    }

    /**
     * Exclui a turma quando nunca teve matrículas.
     */
    public function excluir(int $id)
    {
        $resultado = $this->turmaService->excluir($id);
        $destino   = $resultado['sucesso'] ? url_to('turmas') : url_to('turmas_ver', $id);

        return redirect()->to($destino)->with($resultado['sucesso'] ? 'sucesso' : 'erro', $resultado['mensagem']);
    }

    /**
     * Volta para a turma com a mensagem do resultado da mudança de situação.
     *
     * @param array{sucesso: bool, mensagem: string} $resultado
     */
    private function responderSituacao(int $id, array $resultado)
    {
        return redirect()->to(url_to('turmas_ver', $id))->with($resultado['sucesso'] ? 'sucesso' : 'erro', $resultado['mensagem']);
    }

    /**
     * Monta o formulário compartilhado entre cadastro e edição.
     *
     * @param array<string, mixed> $turma
     */
    private function formulario(string $titulo, array $turma, string $acao)
    {
        return view('turmas/formulario', [
            'titulo'      => $titulo,
            'turma'       => $turma,
            'modalidades' => (new ModalidadeModel())->buscarTodasOrdenadas(),
            'professores' => (new ProfessorModel())->buscarAtivosOrdenados(),
            'acao'        => $acao,
        ]);
    }

    /**
     * Busca a turma ou responde 404.
     *
     * @return array<string, mixed>
     */
    private function buscarOuFalhar(int $id): array
    {
        $turma = $this->turmaModel->find($id);

        if ($turma === null) {
            throw PageNotFoundException::forPageNotFound('Turma não encontrada.');
        }

        return $turma;
    }
}
