<?php

namespace App\Controllers;

use App\Models\AlunoModel;
use App\Models\ResponsavelLegalModel;
use App\Services\AlunoService;

/**
 * Telas do cadastro de alunos (HU01: RF01, RF02, RF03).
 *
 * O Controller só orquestra: a regra mora no AlunoModel e no AlunoService.
 */
class Alunos extends BaseController
{
    private AlunoModel $alunoModel;
    private AlunoService $alunoService;

    public function __construct()
    {
        $this->alunoModel   = new AlunoModel();
        $this->alunoService = new AlunoService($this->alunoModel);
    }

    /**
     * Lista alunos com busca por nome, código ou CPF e paginação.
     */
    public function index()
    {
        $busca = trim((string) $this->request->getGet('busca'));

        return view('alunos/index', [
            'titulo' => 'Alunos',
            'alunos' => $this->alunoModel->buscarAlunos($busca),
            'pager'  => $this->alunoModel->pager,
            'busca'  => $busca,
        ]);
    }

    /**
     * Exibe o formulário de novo aluno.
     */
    public function novo()
    {
        return view('alunos/formulario', [
            'titulo'      => 'Novo aluno',
            'aluno'       => ['data_matricula' => date('Y-m-d')],
            'responsavel' => [],
            'acao'        => url_to('alunos_criar'),
        ]);
    }

    /**
     * Grava um novo aluno (RF01, RF02) e, se menor de idade, o responsável legal (RF03, RN04).
     */
    public function criar()
    {
        $resultado = $this->alunoService->cadastrar($this->request->getPost());

        if (! $resultado['sucesso']) {
            return redirect()->back()->withInput()->with('erros', $resultado['erros']);
        }

        return redirect()->to(url_to('alunos_ver', $resultado['id']))->with('sucesso', 'Aluno cadastrado com sucesso.');
    }

    /**
     * Mostra os dados de um aluno. A condição de saúde só aparece para o proprietário (RNF06).
     */
    public function ver(int $id)
    {
        $aluno = $this->buscarOuFalhar($id);

        return view('alunos/ver', [
            'titulo'      => $aluno['nome'],
            'aluno'       => $this->alunoModel->aplicarRestricaoLgpd($aluno, session()->get('usuario_perfil')),
            'idade'       => $this->alunoModel->calcularIdade($aluno['data_nascimento']),
            'responsavel' => (new ResponsavelLegalModel())->buscarPorAluno($id),
        ]);
    }

    /**
     * Exibe o formulário de edição preenchido com os dados atuais.
     */
    public function editar(int $id)
    {
        $aluno = $this->buscarOuFalhar($id);

        return view('alunos/formulario', [
            'titulo'      => 'Editar aluno',
            'aluno'       => $this->alunoModel->aplicarRestricaoLgpd($aluno, session()->get('usuario_perfil')),
            'responsavel' => (new ResponsavelLegalModel())->buscarPorAluno($id) ?? [],
            'acao'        => url_to('alunos_atualizar', $id),
        ]);
    }

    /**
     * Salva as alterações do aluno (RN06: o código não muda).
     */
    public function atualizar(int $id)
    {
        $this->buscarOuFalhar($id);
        $resultado = $this->alunoService->atualizar($id, $this->request->getPost(), session()->get('usuario_perfil'));

        if (! $resultado['sucesso']) {
            return redirect()->back()->withInput()->with('erros', $resultado['erros']);
        }

        return redirect()->to(url_to('alunos_ver', $id))->with('sucesso', 'Cadastro atualizado com sucesso.');
    }

    /**
     * Inativa o aluno, preservando matrículas, frequência e cobranças.
     */
    public function inativar(int $id)
    {
        $this->buscarOuFalhar($id);
        $this->alunoModel->update($id, ['situacao' => 'INATIVO']);

        return redirect()->to(url_to('alunos_ver', $id))->with('sucesso', 'Aluno inativado.');
    }

    /**
     * Reativa um aluno inativo.
     */
    public function reativar(int $id)
    {
        $this->buscarOuFalhar($id);
        $this->alunoModel->update($id, ['situacao' => 'ATIVO']);

        return redirect()->to(url_to('alunos_ver', $id))->with('sucesso', 'Aluno reativado.');
    }

    /**
     * Exclui o aluno definitivamente quando não há vínculos (só proprietário).
     */
    public function excluir(int $id)
    {
        $resultado = $this->alunoService->excluir($id);

        $destino = $resultado['sucesso'] ? url_to('alunos') : url_to('alunos_ver', $id);

        return redirect()->to($destino)->with($resultado['sucesso'] ? 'sucesso' : 'erro', $resultado['mensagem']);
    }

    /**
     * Busca o aluno ou responde 404 quando o id não existe.
     *
     * @return array<string, mixed>
     */
    private function buscarOuFalhar(int $id): array
    {
        $aluno = $this->alunoModel->find($id);

        if ($aluno === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Aluno não encontrado.');
        }

        return $aluno;
    }
}
