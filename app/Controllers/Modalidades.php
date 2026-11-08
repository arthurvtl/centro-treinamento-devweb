<?php

namespace App\Controllers;

use App\Models\ModalidadeModel;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Telas de modalidades (HU02, RF05). Acesso restrito ao proprietário pelas rotas.
 */
class Modalidades extends BaseController
{
    private ModalidadeModel $modalidadeModel;

    public function __construct()
    {
        $this->modalidadeModel = new ModalidadeModel();
    }

    /**
     * Lista as modalidades cadastradas.
     */
    public function index()
    {
        return view('modalidades/index', [
            'titulo'      => 'Modalidades',
            'modalidades' => $this->modalidadeModel->buscarTodasOrdenadas(),
        ]);
    }

    /**
     * Exibe o formulário de nova modalidade.
     */
    public function novo()
    {
        return view('modalidades/formulario', [
            'titulo'     => 'Nova modalidade',
            'modalidade' => ['capacidade_padrao' => 20],
            'acao'       => url_to('modalidades_criar'),
        ]);
    }

    /**
     * Grava uma nova modalidade (RF05); a capacidade respeita RN01 e RN02.
     */
    public function criar()
    {
        if (! $this->modalidadeModel->insert($this->dadosDoFormulario())) {
            return redirect()->back()->withInput()->with('erros', $this->modalidadeModel->errors());
        }

        return redirect()->to(url_to('modalidades'))->with('sucesso', 'Modalidade cadastrada com sucesso.');
    }

    /**
     * Exibe o formulário de edição preenchido.
     */
    public function editar(int $id)
    {
        return view('modalidades/formulario', [
            'titulo'     => 'Editar modalidade',
            'modalidade' => $this->buscarOuFalhar($id),
            'acao'       => url_to('modalidades_atualizar', $id),
        ]);
    }

    /**
     * Salva as alterações da modalidade.
     */
    public function atualizar(int $id)
    {
        $this->buscarOuFalhar($id);

        // O id vai junto para o {id} do is_unique: a modalidade editada não conta como nome duplicado.
        $dados = $this->dadosDoFormulario() + ['id' => $id];

        if (! $this->modalidadeModel->update($id, $dados)) {
            return redirect()->back()->withInput()->with('erros', $this->modalidadeModel->errors());
        }

        return redirect()->to(url_to('modalidades'))->with('sucesso', 'Modalidade atualizada com sucesso.');
    }

    /**
     * Exclui a modalidade quando não há turmas vinculadas.
     */
    public function excluir(int $id)
    {
        $this->buscarOuFalhar($id);

        if ($this->modalidadeModel->possuiTurmas($id)) {
            return redirect()->to(url_to('modalidades'))->with('erro', 'Esta modalidade tem turmas e não pode ser excluída.');
        }

        $this->modalidadeModel->delete($id);

        return redirect()->to(url_to('modalidades'))->with('sucesso', 'Modalidade excluída.');
    }

    /**
     * Seleciona só os campos que o formulário pode enviar.
     *
     * @return array<string, mixed>
     */
    private function dadosDoFormulario(): array
    {
        return $this->request->getPost(['nome', 'descricao', 'categoria', 'capacidade_padrao']);
    }

    /**
     * Busca a modalidade ou responde 404.
     *
     * @return array<string, mixed>
     */
    private function buscarOuFalhar(int $id): array
    {
        $modalidade = $this->modalidadeModel->find($id);

        if ($modalidade === null) {
            throw PageNotFoundException::forPageNotFound('Modalidade não encontrada.');
        }

        return $modalidade;
    }
}
