<?php

namespace App\Services;

use App\Models\AlunoModel;
use App\Models\MatriculaModel;
use App\Models\MensalidadeModel;
use App\Models\TurmaModel;

/**
 * Monta os indicadores do painel inicial conforme o perfil (HU06, RN05).
 *
 * Todos os números vêm de métodos dos Models; o Controller só repassa a lista pronta para a View.
 */
class IndicadoresService
{
    public function __construct(
        private readonly AlunoModel $alunoModel = new AlunoModel(),
        private readonly MatriculaModel $matriculaModel = new MatriculaModel(),
        private readonly TurmaModel $turmaModel = new TurmaModel(),
        private readonly MensalidadeModel $mensalidadeModel = new MensalidadeModel(),
    ) {
    }

    /**
     * Devolve os indicadores do painel: 3 para a recepção e 4 para o proprietário.
     *
     * Os números financeiros (mensalidades atrasadas e total recebido no mês) só aparecem para o
     * proprietário, porque a recepcionista não acessa relatórios financeiros (RN05). Quem atende no
     * balcão vê, no lugar deles, as turmas que ainda têm vaga.
     *
     * @return list<array{id: string, rotulo: string, valor: string, legenda: string|null, alerta: bool}>
     */
    public function doPainel(string $perfil): array
    {
        helper('formatacao');

        $indicadores = [
            $this->indicador('kpiAlunos', 'Alunos ativos', (string) $this->alunoModel->contarPorSituacao()['ativos'], 'cadastros em uso'),
            $this->indicador('kpiMatriculas', 'Matrículas ativas', (string) $this->matriculaModel->contarAtivas(), 'alunos em turmas'),
        ];

        if ($perfil !== 'proprietario') {
            $indicadores[] = $this->indicador('kpiVagas', 'Turmas com vaga', (string) count($this->turmaModel->buscarComVagasDisponiveis()), 'aceitando matrícula');
        }

        if ($perfil === 'proprietario') {
            $atrasadas = $this->mensalidadeModel->contarAtrasadas();

            $indicadores[] = $this->indicador('kpiAtrasadas', 'Mensalidades atrasadas', (string) $atrasadas, 'vencidas e não pagas', $atrasadas > 0);
            $indicadores[] = $this->indicador(
                'kpiRecebido',
                'Recebido no mês',
                formatar_moeda($this->mensalidadeModel->resumirMes(date('Y-m'))['recebido']),
                'pagamentos do mês atual',
            );
        }

        return $indicadores;
    }

    /**
     * Monta um item da lista de indicadores.
     *
     * @return array{id: string, rotulo: string, valor: string, legenda: string|null, alerta: bool}
     */
    private function indicador(string $id, string $rotulo, string $valor, ?string $legenda, bool $alerta = false): array
    {
        return ['id' => $id, 'rotulo' => $rotulo, 'valor' => $valor, 'legenda' => $legenda, 'alerta' => $alerta];
    }
}
