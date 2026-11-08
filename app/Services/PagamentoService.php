<?php

namespace App\Services;

use App\Models\AlunoModel;
use App\Models\AlunoPlanoModel;
use App\Models\MensalidadeModel;
use App\Models\PlanoModel;

/**
 * Fluxos financeiros do aluno: plano, mensalidade e quitação (RF13, RF14, RF24, RF25, RN08, RN11, HU05).
 *
 * A regra não depende do perfil: quem pode chamar cada fluxo é decidido pelas rotas (RN05).
 */
class PagamentoService
{
    public function __construct(
        private readonly AlunoPlanoModel $alunoPlanoModel = new AlunoPlanoModel(),
        private readonly MensalidadeModel $mensalidadeModel = new MensalidadeModel(),
        private readonly PlanoModel $planoModel = new PlanoModel(),
        private readonly AlunoModel $alunoModel = new AlunoModel(),
    ) {
    }

    /**
     * Vincula o aluno a um plano, mantendo sempre um único plano ativo (RF13, RN08).
     *
     * Se o aluno já tem plano ativo diferente, o anterior é ENCERRADO na mesma transação em
     * que o novo nasce: assim nunca existem dois ativos nem um intervalo sem plano.
     *
     * @return array{sucesso: bool, mensagem: string}
     */
    public function vincularPlano(int $alunoId, int $planoId): array
    {
        $aluno = $this->alunoModel->find($alunoId);
        $plano = $this->planoModel->find($planoId);

        if ($aluno === null || $plano === null) {
            return ['sucesso' => false, 'mensagem' => 'Aluno ou plano não encontrados.'];
        }

        if ($aluno['situacao'] !== 'ATIVO' || $plano['situacao'] !== 'ATIVO') {
            return ['sucesso' => false, 'mensagem' => 'Aluno e plano precisam estar ativos para o vínculo.'];
        }

        $atual = $this->alunoPlanoModel->buscarAtivoDoAluno($alunoId);
        if ($atual !== null && (int) $atual['plano_id'] === $planoId) {
            return ['sucesso' => false, 'mensagem' => 'O aluno já está neste plano.'];
        }

        $hoje  = date('Y-m-d');
        $banco = db_connect();
        $banco->transStart();
        if ($atual !== null) {
            $this->alunoPlanoModel->update($atual['id'], ['situacao' => 'ENCERRADO', 'data_fim' => $hoje]);
        }
        $this->alunoPlanoModel->insert([
            'aluno_id'    => $alunoId,
            'plano_id'    => $planoId,
            'data_inicio' => $hoje,
            'situacao'    => 'ATIVO',
        ]);
        $banco->transComplete();

        return $banco->transStatus()
            ? ['sucesso' => true, 'mensagem' => 'Plano "' . $plano['nome'] . '" vinculado ao aluno.']
            : ['sucesso' => false, 'mensagem' => 'Não foi possível vincular o plano. Tente novamente.'];
    }

    /**
     * Gera uma mensalidade para o plano ativo do aluno (RF14).
     *
     * Usa o valor do plano quando nenhum valor é informado e barra duas mensalidades com o
     * mesmo vencimento. Vencimento já passado nasce em aberto e vira ATRASADO na próxima consulta (RN07).
     *
     * @param array<string, mixed> $dados vencimento e, opcionalmente, valor
     * @return array{sucesso: bool, erros: array<string, string>}
     */
    public function criarMensalidade(int $alunoId, array $dados): array
    {
        $vinculo = $this->alunoPlanoModel->buscarAtivoDoAluno($alunoId);
        if ($vinculo === null) {
            return ['sucesso' => false, 'erros' => ['geral' => 'O aluno não tem plano ativo. Vincule um plano antes de gerar mensalidades.']];
        }

        $valor      = trim((string) ($dados['valor'] ?? ''));
        $mensalidade = [
            'aluno_plano_id' => (int) $vinculo['id'],
            'valor'          => $valor === '' ? $vinculo['plano_valor'] : $this->planoModel->normalizarValor(['valor' => $valor])['valor'],
            'vencimento'     => (string) ($dados['vencimento'] ?? ''),
            'situacao'       => 'EM_ABERTO',
        ];

        if (! $this->mensalidadeModel->validate($mensalidade)) {
            return ['sucesso' => false, 'erros' => $this->mensalidadeModel->errors()];
        }

        if ($this->mensalidadeModel->existeNoVencimento((int) $vinculo['id'], $mensalidade['vencimento'])) {
            return ['sucesso' => false, 'erros' => ['vencimento' => 'Já existe mensalidade deste plano com esse vencimento.']];
        }

        $this->mensalidadeModel->insert($mensalidade);

        return ['sucesso' => true, 'erros' => []];
    }

    /**
     * Registra o pagamento de uma mensalidade, que passa a PAGO (RF14, RF24, RF25).
     *
     * A adimplência do aluno é recalculada a partir das mensalidades (RN11), então não há campo a atualizar:
     * ao quitar o último atraso, o aluno volta a aparecer como adimplente em todas as telas.
     *
     * @param array<string, mixed> $dados data_pagamento e forma_pagamento
     * @return array{sucesso: bool, mensagem: string}
     */
    public function quitar(int $mensalidadeId, array $dados): array
    {
        $this->mensalidadeModel->atualizarAtrasadas();
        $mensalidade = $this->mensalidadeModel->find($mensalidadeId);

        if ($mensalidade === null) {
            return ['sucesso' => false, 'mensagem' => 'Mensalidade não encontrada.'];
        }

        if ($mensalidade['situacao'] === 'PAGO') {
            return ['sucesso' => false, 'mensagem' => 'Esta mensalidade já está quitada.'];
        }

        $pagamento = [
            'data_pagamento'  => (string) ($dados['data_pagamento'] ?? ''),
            'forma_pagamento' => (string) ($dados['forma_pagamento'] ?? ''),
        ];

        $erros = $this->mensalidadeModel->validarQuitacao($pagamento);
        if ($erros !== []) {
            return ['sucesso' => false, 'mensagem' => implode(' ', $erros)];
        }

        $this->mensalidadeModel->update($mensalidadeId, $pagamento + ['situacao' => 'PAGO']);

        return ['sucesso' => true, 'mensagem' => 'Pagamento registrado.'];
    }

    /**
     * Informa se o aluno está adimplente, para exibir a situação do plano (RF24, RN11).
     */
    public function alunoEstaAdimplente(int $alunoId): bool
    {
        return $this->mensalidadeModel->alunoEstaAdimplente($alunoId);
    }

    /**
     * Corrige valor e vencimento de uma mensalidade que ainda não foi paga (RF14).
     *
     * Mensalidade quitada não muda: ela é o registro do pagamento (RF24). Depois da correção a
     * mensalidade volta a EM_ABERTO e, se o novo vencimento já passou, a próxima consulta a marca
     * como ATRASADO (RN07).
     *
     * @param array<string, mixed> $dados valor e vencimento
     * @return array{sucesso: bool, erros: array<string, string>, alunoId: int|null}
     */
    public function atualizarMensalidade(int $mensalidadeId, array $dados): array
    {
        $mensalidade = $this->mensalidadeModel->find($mensalidadeId);

        if ($mensalidade === null) {
            return ['sucesso' => false, 'erros' => ['geral' => 'Mensalidade não encontrada.'], 'alunoId' => null];
        }

        $alunoId = (int) $this->alunoPlanoModel->find($mensalidade['aluno_plano_id'])['aluno_id'];

        if ($mensalidade['situacao'] === 'PAGO') {
            return ['sucesso' => false, 'erros' => ['geral' => 'Mensalidade quitada não pode ser alterada: ela é o registro do pagamento.'], 'alunoId' => $alunoId];
        }

        $novos = [
            'aluno_plano_id' => (int) $mensalidade['aluno_plano_id'],
            'valor'          => $this->planoModel->normalizarValor(['valor' => (string) ($dados['valor'] ?? '')])['valor'],
            'vencimento'     => (string) ($dados['vencimento'] ?? ''),
            'situacao'       => 'EM_ABERTO',
        ];

        if (! $this->mensalidadeModel->validate($novos)) {
            return ['sucesso' => false, 'erros' => $this->mensalidadeModel->errors(), 'alunoId' => $alunoId];
        }

        if ($this->mensalidadeModel->existeNoVencimento($novos['aluno_plano_id'], $novos['vencimento'], $mensalidadeId)) {
            return ['sucesso' => false, 'erros' => ['vencimento' => 'Já existe outra mensalidade deste plano com esse vencimento.'], 'alunoId' => $alunoId];
        }

        $this->mensalidadeModel->update($mensalidadeId, ['valor' => $novos['valor'], 'vencimento' => $novos['vencimento'], 'situacao' => 'EM_ABERTO']);

        return ['sucesso' => true, 'erros' => [], 'alunoId' => $alunoId];
    }

    /**
     * Exclui uma mensalidade ainda não paga, gerada por engano (RF14).
     *
     * @return array{sucesso: bool, mensagem: string, alunoId: int|null}
     */
    public function excluirMensalidade(int $mensalidadeId): array
    {
        $mensalidade = $this->mensalidadeModel->find($mensalidadeId);

        if ($mensalidade === null) {
            return ['sucesso' => false, 'mensagem' => 'Mensalidade não encontrada.', 'alunoId' => null];
        }

        $alunoId = (int) $this->alunoPlanoModel->find($mensalidade['aluno_plano_id'])['aluno_id'];

        if ($mensalidade['situacao'] === 'PAGO') {
            return ['sucesso' => false, 'mensagem' => 'Mensalidade quitada não pode ser excluída: ela faz parte do histórico de quitações.', 'alunoId' => $alunoId];
        }

        $this->mensalidadeModel->delete($mensalidadeId);

        return ['sucesso' => true, 'mensagem' => 'Mensalidade excluída.', 'alunoId' => $alunoId];
    }
}
