<?php

namespace App\Controllers;

use App\Models\PlanoModel;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Telas de planos de pagamento (HU05, RF12). Acesso restrito ao proprietário pelas rotas.
 */
class Planos extends BaseController
{
    private PlanoModel $planoModel;

    public function __construct()
    {
        $this->planoModel = new PlanoModel();
    }

    /**
     * Lista os planos cadastrados.
     */
    public function index()
    {
        return view('planos/index', [
            'titulo' => 'Planos',
            'planos' => $this->planoModel->buscarTodosOrdenados(),
        ]);
    }

    /**
     * Exibe o formulário de novo plano.
     */
    public function novo()
    {
        return view('planos/formulario', [
            'titulo' => 'Novo plano',
            'plano'  => [],
            'acao'   => url_to('planos_criar'),
        ]);
    }

    /**
     * Grava um novo plano, sempre começando ATIVO (RF12).
     */
    public function criar()
    {
        $dados = $this->planoModel->normalizarValor($this->dadosDoFormulario()) + ['situacao' => 'ATIVO'];

        if (! $this->planoModel->insert($dados)) {
            return redirect()->back()->withInput()->with('erros', $this->planoModel->errors());
        }

        return redirect()->to(url_to('planos'))->with('sucesso', 'Plano cadastrado com sucesso.');
    }

    /**
     * Exibe o formulário de edição preenchido.
     */
    public function editar(int $id)
    {
        return view('planos/formulario', [
            'titulo' => 'Editar plano',
            'plano'  => $this->buscarOuFalhar($id),
            'acao'   => url_to('planos_atualizar', $id),
        ]);
    }

    /**
     * Salva as alterações do plano.
     */
    public function atualizar(int $id)
    {
        $this->buscarOuFalhar($id);
        $dados = $this->planoModel->normalizarValor($this->dadosDoFormulario());

        if (! $this->planoModel->update($id, $dados)) {
            return redirect()->back()->withInput()->with('erros', $this->planoModel->errors());
        }

        return redirect()->to(url_to('planos'))->with('sucesso', 'Plano atualizado com sucesso.');
    }

    /**
     * Inativa o plano: ele deixa de ser oferecido, mas os vínculos existentes continuam.
     */
    public function inativar(int $id)
    {
        $this->buscarOuFalhar($id);
        $this->planoModel->update($id, ['situacao' => 'INATIVO']);

        return redirect()->to(url_to('planos'))->with('sucesso', 'Plano inativado.');
    }

    /**
     * Reativa um plano inativo.
     */
    public function reativar(int $id)
    {
        $this->buscarOuFalhar($id);
        $this->planoModel->update($id, ['situacao' => 'ATIVO']);

        return redirect()->to(url_to('planos'))->with('sucesso', 'Plano reativado.');
    }

    /**
     * Exclui o plano quando nenhum aluno foi vinculado a ele.
     */
    public function excluir(int $id)
    {
        $this->buscarOuFalhar($id);

        if ($this->planoModel->possuiAlunos($id)) {
            return redirect()->to(url_to('planos'))->with('erro', 'Este plano já foi usado por alunos. Inative em vez de excluir.');
        }

        $this->planoModel->delete($id);

        return redirect()->to(url_to('planos'))->with('sucesso', 'Plano excluído.');
    }

    /**
     * Seleciona só os campos que o formulário pode enviar.
     *
     * @return array<string, mixed>
     */
    private function dadosDoFormulario(): array
    {
        return $this->request->getPost(['nome', 'valor', 'periodicidade', 'beneficios']);
    }

    /**
     * Busca o plano ou responde 404.
     *
     * @return array<string, mixed>
     */
    private function buscarOuFalhar(int $id): array
    {
        $plano = $this->planoModel->find($id);

        if ($plano === null) {
            throw PageNotFoundException::forPageNotFound('Plano não encontrado.');
        }

        return $plano;
    }
}
