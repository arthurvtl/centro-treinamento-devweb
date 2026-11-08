<?php

namespace App\Controllers;

use App\Models\AlunoModel;
use App\Models\MensalidadeModel;
use App\Models\ModalidadeModel;
use App\Models\TurmaModel;

/**
 * Relatórios de consulta (HU06: RF16, RF17, RF18, RF19).
 *
 * Os relatórios operacionais servem aos dois perfis; o financeiro é restrito ao proprietário
 * pela rota (RN05).
 */
class Relatorios extends BaseController
{
    /**
     * Painel com o total de alunos (RF16), alunos por modalidade (RF17) e turmas com vagas (RF18).
     */
    public function index()
    {
        return view('relatorios/index', [
            'titulo'        => 'Relatórios',
            'totais'        => (new AlunoModel())->contarPorSituacao(),
            'modalidades'   => (new ModalidadeModel())->contarAlunosPorModalidade(),
            'turmasComVaga' => (new TurmaModel())->buscarComVagasDisponiveis(),
        ]);
    }

    /**
     * Relatório financeiro de um mês (RF19), escolhido por `?mes=AAAA-MM`; padrão: mês atual.
     */
    public function financeiro()
    {
        $mensalidadeModel = new MensalidadeModel();
        $mes              = $mensalidadeModel->normalizarMes($this->request->getGet('mes'));

        return view('relatorios/financeiro', [
            'titulo'  => 'Relatório financeiro',
            'mes'     => $mes,
            'resumo'  => $mensalidadeModel->resumirMes($mes),
        ]);
    }
}
