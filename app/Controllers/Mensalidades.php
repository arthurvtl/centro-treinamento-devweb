<?php

namespace App\Controllers;

use App\Models\AlunoModel;
use App\Models\AlunoPlanoModel;
use App\Models\MensalidadeModel;
use App\Models\PlanoModel;
use App\Services\PagamentoService;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Telas financeiras: mensalidades, quitação, inadimplentes e situação por aluno
 * (HU05: RF13, RF14, RF15, RF24, RF25).
 *
 * Permissões (RN05), definidas nas rotas: a recepcionista vê o financeiro de um aluno, vincula plano e
 * registra quitação, porque isso é atendimento de balcão e não um relatório financeiro restrito.
 * Lista geral de mensalidades, inadimplentes e geração de cobranças ficam só com o proprietário.
 */
class Mensalidades extends BaseController
{
    private MensalidadeModel $mensalidadeModel;
    private PagamentoService $pagamentoService;

    public function __construct()
    {
        $this->mensalidadeModel = new MensalidadeModel();
        $this->pagamentoService = new PagamentoService(mensalidadeModel: $this->mensalidadeModel);
    }

    /**
     * Lista todas as mensalidades com filtro por situação e busca por aluno (só proprietário).
     */
    public function index()
    {
        $situacao = (string) $this->request->getGet('situacao');
        $busca    = trim((string) $this->request->getGet('busca'));

        return view('mensalidades/index', [
            'titulo'       => 'Mensalidades',
            'mensalidades' => $this->mensalidadeModel->buscarComDetalhes($situacao, $busca),
            'pager'        => $this->mensalidadeModel->pager,
            'situacao'     => $situacao,
            'busca'        => $busca,
        ]);
    }

    /**
     * Lista alunos com mensalidades vencidas e não pagas (RF15). Só proprietário: é análise de carteira.
     */
    public function inadimplentes()
    {
        return view('mensalidades/inadimplentes', [
            'titulo'        => 'Inadimplentes',
            'inadimplentes' => $this->mensalidadeModel->buscarInadimplentes(),
        ]);
    }

    /**
     * Mostra a situação financeira do aluno: plano ativo, adimplência e histórico de quitações (RF24).
     * Liberada à recepcionista para o atendimento do aluno no balcão.
     */
    public function aluno(int $alunoId)
    {
        $aluno = (new AlunoModel())->buscarResumo($alunoId);

        if ($aluno === null) {
            throw PageNotFoundException::forPageNotFound('Aluno não encontrado.');
        }

        return view('mensalidades/aluno', [
            'titulo'       => 'Financeiro de ' . $aluno['nome'],
            'aluno'        => $aluno,
            'planoAtivo'   => (new AlunoPlanoModel())->buscarAtivoDoAluno($alunoId),
            'adimplente'   => $this->pagamentoService->alunoEstaAdimplente($alunoId),
            'mensalidades' => $this->mensalidadeModel->buscarDoAluno($alunoId),
            'planos'       => (new PlanoModel())->buscarAtivosOrdenados(),
        ]);
    }

    /**
     * Vincula o aluno a um plano, encerrando o anterior (RF13, RN08). Faz parte do atendimento ao aluno.
     */
    public function vincularPlano(int $alunoId)
    {
        $resultado = $this->pagamentoService->vincularPlano($alunoId, (int) $this->request->getPost('plano_id'));

        $retorno = redirect()->to(url_to('mensalidades_aluno', $alunoId));

        if (! $resultado['sucesso']) {
            $retorno->withInput();
        }

        return $retorno->with($resultado['sucesso'] ? 'sucesso' : 'erro', $resultado['mensagem']);
    }

    /**
     * Gera uma mensalidade para o plano ativo do aluno (RF14). Só proprietário: define cobranças.
     */
    public function criar(int $alunoId)
    {
        $resultado = $this->pagamentoService->criarMensalidade($alunoId, $this->request->getPost(['vencimento', 'valor']));

        if (! $resultado['sucesso']) {
            return redirect()->back()->withInput()->with('erros', $resultado['erros']);
        }

        return redirect()->to(url_to('mensalidades_aluno', $alunoId))->with('sucesso', 'Mensalidade gerada.');
    }

    /**
     * Exibe o formulário para corrigir valor e vencimento de uma mensalidade em aberto (só proprietário).
     */
    public function editar(int $id)
    {
        $mensalidade = $this->mensalidadeModel->buscarDetalhe($id);

        if ($mensalidade === null) {
            throw PageNotFoundException::forPageNotFound('Mensalidade não encontrada.');
        }

        // Mensalidade quitada é o registro do pagamento: nem abre para edição (RF24).
        if ($mensalidade['situacao'] === 'PAGO') {
            return redirect()->to(url_to('mensalidades_aluno', $mensalidade['aluno_id']))
                ->with('erro', 'Mensalidade quitada não pode ser alterada: ela é o registro do pagamento.');
        }

        return view('mensalidades/formulario', ['titulo' => 'Editar mensalidade', 'mensalidade' => $mensalidade]);
    }

    /**
     * Salva a correção da mensalidade em aberto (RF14).
     */
    public function atualizar(int $id)
    {
        $resultado = $this->pagamentoService->atualizarMensalidade($id, $this->request->getPost(['valor', 'vencimento']));

        if (! $resultado['sucesso']) {
            return redirect()->back()->withInput()->with('erros', $resultado['erros']);
        }

        return redirect()->to(url_to('mensalidades_aluno', $resultado['alunoId']))->with('sucesso', 'Mensalidade atualizada.');
    }

    /**
     * Exclui uma mensalidade não paga, gerada por engano (só proprietário).
     */
    public function excluir(int $id)
    {
        $resultado = $this->pagamentoService->excluirMensalidade($id);
        $destino   = $resultado['alunoId'] === null ? url_to('mensalidades') : url_to('mensalidades_aluno', $resultado['alunoId']);

        return redirect()->to($destino)->with($resultado['sucesso'] ? 'sucesso' : 'erro', $resultado['mensagem']);
    }

    /**
     * Registra a quitação de uma mensalidade (RF14, RF25) e volta para a tela de origem.
     * A recepcionista também dá baixa: receber o pagamento no balcão é operação de atendimento, não relatório restrito (RN05).
     */
    public function quitar(int $id)
    {
        $resultado = $this->pagamentoService->quitar($id, $this->request->getPost(['data_pagamento', 'forma_pagamento']));
        $retorno   = redirect()->back();

        if (! $resultado['sucesso']) {
            $retorno->withInput();
        }

        return $retorno->with($resultado['sucesso'] ? 'sucesso' : 'erro', $resultado['mensagem']);
    }
}
