<?php

namespace App\Controllers;

use App\Models\ModalidadeModel;
use App\Models\ProfessorModel;
use App\Services\ProfessorService;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Telas de professores e suas habilitações por modalidade (HU02, RF04, RN03).
 * Acesso restrito ao proprietário pelas rotas.
 */
class Professores extends BaseController
{
    private ProfessorModel $professorModel;
    private ProfessorService $professorService;

    public function __construct()
    {
        $this->professorModel   = new ProfessorModel();
        $this->professorService = new ProfessorService($this->professorModel);
    }

    /**
     * Lista professores com as modalidades que podem ministrar.
     */
    public function index()
    {
        return view('professores/index', [
            'titulo'      => 'Professores',
            'professores' => $this->professorModel->buscarComModalidades(),
        ]);
    }

    /**
     * Exibe o formulário de novo professor.
     */
    public function novo()
    {
        return $this->formulario('Novo professor', [], [], url_to('professores_criar'));
    }

    /**
     * Grava um novo professor com suas habilitações (RF04).
     */
    public function criar()
    {
        $resultado = $this->professorService->cadastrar($this->request->getPost());

        if (! $resultado['sucesso']) {
            return redirect()->back()->withInput()->with('erros', $resultado['erros']);
        }

        return redirect()->to(url_to('professores'))->with('sucesso', 'Professor cadastrado com sucesso.');
    }

    /**
     * Exibe o formulário de edição preenchido, com as habilitações atuais marcadas.
     */
    public function editar(int $id)
    {
        $professor = $this->buscarOuFalhar($id);

        return $this->formulario(
            'Editar professor',
            $professor,
            $this->professorModel->buscarIdsModalidades($id),
            url_to('professores_atualizar', $id),
        );
    }

    /**
     * Salva as alterações do professor e de suas habilitações.
     */
    public function atualizar(int $id)
    {
        $this->buscarOuFalhar($id);
        $resultado = $this->professorService->atualizar($id, $this->request->getPost());

        if (! $resultado['sucesso']) {
            return redirect()->back()->withInput()->with('erros', $resultado['erros']);
        }

        return redirect()->to(url_to('professores'))->with('sucesso', 'Professor atualizado com sucesso.');
    }

    /**
     * Inativa o professor, preservando o histórico de turmas.
     */
    public function inativar(int $id)
    {
        $this->buscarOuFalhar($id);
        $this->professorModel->update($id, ['situacao' => 'INATIVO']);

        return redirect()->to(url_to('professores'))->with('sucesso', 'Professor inativado.');
    }

    /**
     * Reativa um professor inativo.
     */
    public function reativar(int $id)
    {
        $this->buscarOuFalhar($id);
        $this->professorModel->update($id, ['situacao' => 'ATIVO']);

        return redirect()->to(url_to('professores'))->with('sucesso', 'Professor reativado.');
    }

    /**
     * Exclui o professor quando não há turmas vinculadas.
     */
    public function excluir(int $id)
    {
        $resultado = $this->professorService->excluir($id);

        return redirect()->to(url_to('professores'))->with($resultado['sucesso'] ? 'sucesso' : 'erro', $resultado['mensagem']);
    }

    /**
     * Monta a tela do formulário, compartilhada entre cadastro e edição.
     *
     * @param array<string, mixed> $professor
     * @param list<int>            $modalidadesMarcadas
     */
    private function formulario(string $titulo, array $professor, array $modalidadesMarcadas, string $acao)
    {
        return view('professores/formulario', [
            'titulo'              => $titulo,
            'professor'           => $professor,
            'modalidades'         => (new ModalidadeModel())->buscarTodasOrdenadas(),
            'modalidadesMarcadas' => $modalidadesMarcadas,
            'acao'                => $acao,
        ]);
    }

    /**
     * Busca o professor ou responde 404.
     *
     * @return array<string, mixed>
     */
    private function buscarOuFalhar(int $id): array
    {
        $professor = $this->professorModel->find($id);

        if ($professor === null) {
            throw PageNotFoundException::forPageNotFound('Professor não encontrado.');
        }

        return $professor;
    }
}
