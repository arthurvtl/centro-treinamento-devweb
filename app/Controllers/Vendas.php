<?php

namespace App\Controllers;

use App\Models\AlunoModel;
use App\Models\ProdutoModel;
use App\Models\VendaModel;
use App\Services\VendaService;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Telas da loja: histórico de vendas, nova venda no balcão e cancelamento (RF27, RF28).
 */
class Vendas extends BaseController
{
    private VendaModel $vendaModel;

    public function __construct()
    {
        $this->vendaModel = new VendaModel();
    }

    /**
     * Lista as vendas, da mais recente para a mais antiga, com o total de hoje.
     */
    public function index()
    {
        $vendas = $this->vendaModel->buscarComDetalhes();

        return view('vendas/index', [
            'titulo' => 'Loja',
            'vendas' => $vendas,
            'pager'  => $this->vendaModel->pager,
            'hoje'   => $this->vendaModel->resumirHoje(),
            'baixos' => (new ProdutoModel())->buscarEstoqueBaixo(),
        ]);
    }

    /**
     * Tela de venda no balcão: lista os produtos disponíveis para informar as quantidades.
     */
    public function nova()
    {
        return view('vendas/nova', [
            'titulo'   => 'Nova venda',
            'produtos' => (new ProdutoModel())->buscarVendaveis(),
            'alunos'   => (new AlunoModel())->buscarAtivosParaSelecao(),
        ]);
    }

    /**
     * Registra a venda: baixa o estoque e guarda os preços do momento (RF27).
     */
    public function criar()
    {
        $alunoId   = (int) $this->request->getPost('aluno_id');
        $resultado = (new VendaService())->registrar(
            (array) $this->request->getPost('quantidade'),
            $alunoId > 0 ? $alunoId : null,
            (string) $this->request->getPost('forma_pagamento'),
            (int) session()->get('usuario_id'),
        );

        if (! $resultado['sucesso']) {
            return redirect()->back()->withInput()->with('erro', $resultado['mensagem']);
        }

        return redirect()->to(url_to('vendas_ver', $resultado['venda_id']))->with('sucesso', $resultado['mensagem']);
    }

    /**
     * Mostra os detalhes da venda com os produtos vendidos.
     */
    public function ver(int $id)
    {
        $venda = $this->vendaModel->buscarDetalhe($id);

        if ($venda === null) {
            throw PageNotFoundException::forPageNotFound('Venda não encontrada.');
        }

        return view('vendas/ver', [
            'titulo' => 'Venda #' . $id,
            'venda'  => $venda,
            'itens'  => $this->vendaModel->buscarItens($id),
        ]);
    }

    /**
     * Cancela a venda e devolve os produtos ao estoque (RF28).
     */
    public function cancelar(int $id)
    {
        $resultado = (new VendaService())->cancelar($id);

        return redirect()->to(url_to('vendas_ver', $id))->with($resultado['sucesso'] ? 'sucesso' : 'erro', $resultado['mensagem']);
    }
}
