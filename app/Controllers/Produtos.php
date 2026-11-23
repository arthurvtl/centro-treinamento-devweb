<?php

namespace App\Controllers;

use App\Models\ProdutoModel;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Telas do catálogo e do estoque da loja (RF26). Liberadas à recepção e ao proprietário.
 */
class Produtos extends BaseController
{
    private ProdutoModel $produtoModel;

    public function __construct()
    {
        $this->produtoModel = new ProdutoModel();
    }

    /**
     * Lista os produtos com estoque, destacando os que precisam de reposição.
     */
    public function index()
    {
        return view('produtos/index', [
            'titulo'   => 'Produtos',
            'produtos' => $this->produtoModel->buscarTodosOrdenados(),
        ]);
    }

    /**
     * Exibe o formulário de novo produto.
     */
    public function novo()
    {
        return view('produtos/formulario', [
            'titulo'  => 'Novo produto',
            'produto' => [],
            'acao'    => url_to('produtos_criar'),
        ]);
    }

    /**
     * Grava um novo produto, sempre começando ATIVO.
     */
    public function criar()
    {
        $dados = $this->produtoModel->normalizarPrecos($this->dadosDoFormulario()) + ['situacao' => 'ATIVO'];

        if (! $this->produtoModel->insert($dados)) {
            return redirect()->back()->withInput()->with('erros', $this->produtoModel->errors());
        }

        return redirect()->to(url_to('produtos'))->with('sucesso', 'Produto cadastrado com sucesso.');
    }

    /**
     * Exibe o formulário de edição preenchido (inclui o ajuste de estoque, para repor mercadoria).
     */
    public function editar(int $id)
    {
        return view('produtos/formulario', [
            'titulo'  => 'Editar produto',
            'produto' => $this->buscarOuFalhar($id),
            'acao'    => url_to('produtos_atualizar', $id),
        ]);
    }

    /**
     * Salva as alterações do produto.
     */
    public function atualizar(int $id)
    {
        $this->buscarOuFalhar($id);
        $dados = $this->produtoModel->normalizarPrecos($this->dadosDoFormulario());

        if (! $this->produtoModel->update($id, $dados)) {
            return redirect()->back()->withInput()->with('erros', $this->produtoModel->errors());
        }

        return redirect()->to(url_to('produtos'))->with('sucesso', 'Produto atualizado com sucesso.');
    }

    /**
     * Inativa o produto: ele deixa de aparecer na venda, mas o histórico continua.
     */
    public function inativar(int $id)
    {
        $this->buscarOuFalhar($id);
        $this->produtoModel->update($id, ['situacao' => 'INATIVO']);

        return redirect()->to(url_to('produtos'))->with('sucesso', 'Produto inativado.');
    }

    /**
     * Reativa um produto inativo.
     */
    public function reativar(int $id)
    {
        $this->buscarOuFalhar($id);
        $this->produtoModel->update($id, ['situacao' => 'ATIVO']);

        return redirect()->to(url_to('produtos'))->with('sucesso', 'Produto reativado.');
    }

    /**
     * Exclui o produto quando ele nunca foi vendido; senão, orienta a inativar.
     */
    public function excluir(int $id)
    {
        $this->buscarOuFalhar($id);

        if ($this->produtoModel->possuiVendas($id)) {
            return redirect()->to(url_to('produtos'))->with('erro', 'Este produto já foi vendido. Inative em vez de excluir.');
        }

        $this->produtoModel->delete($id);

        return redirect()->to(url_to('produtos'))->with('sucesso', 'Produto excluído.');
    }

    /**
     * Seleciona só os campos que o formulário pode enviar.
     *
     * @return array<string, mixed>
     */
    private function dadosDoFormulario(): array
    {
        return $this->request->getPost(['nome', 'categoria', 'preco_custo', 'preco_venda', 'estoque_atual', 'estoque_minimo']);
    }

    /**
     * Busca o produto ou responde 404.
     *
     * @return array<string, mixed>
     */
    private function buscarOuFalhar(int $id): array
    {
        $produto = $this->produtoModel->find($id);

        if ($produto === null) {
            throw PageNotFoundException::forPageNotFound('Produto não encontrado.');
        }

        return $produto;
    }
}
