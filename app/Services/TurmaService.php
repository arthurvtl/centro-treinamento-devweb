<?php

namespace App\Services;

use App\Models\TurmaModel;

/**
 * Regras de cadastro e de situação das turmas (RF06, RF22, RN03, RN10).
 *
 * O encerramento de turma (RF23) tem serviço próprio: envolve matrículas, frequência e alunos.
 */
class TurmaService
{
    public function __construct(private readonly TurmaModel $turmaModel = new TurmaModel())
    {
    }

    /**
     * Cadastra uma turma nova, sempre começando ATIVA (RF06).
     *
     * @param array<string, mixed> $dados dados brutos do formulário
     * @return array{sucesso: bool, erros: array<string, string>, id: int|null}
     */
    public function cadastrar(array $dados): array
    {
        $turma             = $this->normalizar($dados);
        $turma['situacao'] = 'ATIVA';

        $id = $this->turmaModel->insert($turma, true);
        if ($id === false) {
            return ['sucesso' => false, 'erros' => $this->turmaModel->errors(), 'id' => null];
        }

        return ['sucesso' => true, 'erros' => [], 'id' => (int) $id];
    }

    /**
     * Atualiza uma turma que ainda não foi encerrada (RF06).
     *
     * Barra reduzir o limite de vagas abaixo das matrículas ativas, para não deixar a turma
     * "lotada além do limite" com alunos já matriculados.
     *
     * @param array<string, mixed> $dados dados brutos do formulário
     * @return array{sucesso: bool, erros: array<string, string>}
     */
    public function atualizar(int $id, array $dados): array
    {
        $atual = $this->turmaModel->find($id);
        if ($atual === null || $atual['situacao'] === 'ENCERRADA') {
            return ['sucesso' => false, 'erros' => ['geral' => 'Turma encerrada não pode ser alterada.']];
        }

        $turma = $this->normalizar($dados);

        $ocupadas = $this->turmaModel->contarMatriculasAtivas($id);
        if ((int) ($turma['limite_vagas'] ?? 0) < $ocupadas) {
            return ['sucesso' => false, 'erros' => ['limite_vagas' => "A turma já tem {$ocupadas} alunos matriculados; o limite não pode ser menor."]];
        }

        if (! $this->turmaModel->update($id, $turma)) {
            return ['sucesso' => false, 'erros' => $this->turmaModel->errors()];
        }

        return ['sucesso' => true, 'erros' => []];
    }

    /**
     * Suspende uma turma ATIVA (RF22, RN10).
     *
     * Só a situação muda: matrículas e vagas ficam preservadas, e a turma passa a recusar
     * novas matrículas e lançamentos de frequência.
     *
     * @return array{sucesso: bool, mensagem: string}
     */
    public function suspender(int $id): array
    {
        return $this->mudarSituacao($id, 'ATIVA', 'SUSPENSA', 'Turma suspensa.', 'Só turmas ativas podem ser suspensas.');
    }

    /**
     * Reativa uma turma SUSPENSA (RF22).
     *
     * @return array{sucesso: bool, mensagem: string}
     */
    public function reativar(int $id): array
    {
        return $this->mudarSituacao($id, 'SUSPENSA', 'ATIVA', 'Turma reativada.', 'Só turmas suspensas podem ser reativadas.');
    }

    /**
     * Exclui a turma, desde que nunca tenha recebido matrículas.
     *
     * @return array{sucesso: bool, mensagem: string}
     */
    public function excluir(int $id): array
    {
        if ($this->turmaModel->find($id) === null) {
            return ['sucesso' => false, 'mensagem' => 'Turma não encontrada.'];
        }

        if ($this->turmaModel->possuiMatriculas($id)) {
            return ['sucesso' => false, 'mensagem' => 'Esta turma tem matrículas. Suspenda ou encerre em vez de excluir.'];
        }

        $this->turmaModel->delete($id);

        return ['sucesso' => true, 'mensagem' => 'Turma excluída.'];
    }

    /**
     * Troca a situação da turma só quando ela está na situação de origem esperada.
     *
     * @return array{sucesso: bool, mensagem: string}
     */
    private function mudarSituacao(int $id, string $de, string $para, string $mensagemOk, string $mensagemErro): array
    {
        $turma = $this->turmaModel->find($id);

        if ($turma === null) {
            return ['sucesso' => false, 'mensagem' => 'Turma não encontrada.'];
        }

        if ($turma['situacao'] !== $de) {
            return ['sucesso' => false, 'mensagem' => $mensagemErro];
        }

        $this->turmaModel->update($id, ['situacao' => $para]);

        return ['sucesso' => true, 'mensagem' => $mensagemOk];
    }

    /**
     * Seleciona os campos da turma e junta os dias marcados numa lista como `SEG,QUA,SEX`.
     *
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    private function normalizar(array $dados): array
    {
        $turma = array_intersect_key($dados, array_flip([
            'modalidade_id', 'professor_id', 'nome', 'hora_inicio', 'hora_termino',
            'limite_vagas', 'data_inicio', 'data_termino',
        ]));

        // Mantém a ordem da semana e descarta valores que não sejam dias válidos.
        $marcados = is_array($dados['dias_semana'] ?? null) ? $dados['dias_semana'] : [];
        $turma['dias_semana'] = implode(',', array_values(array_intersect(array_keys(TurmaModel::DIAS_SEMANA), $marcados)));

        // Data de término em branco vira NULL (turma sem fim definido).
        if (($turma['data_termino'] ?? '') === '') {
            $turma['data_termino'] = null;
        }

        return $turma;
    }
}
