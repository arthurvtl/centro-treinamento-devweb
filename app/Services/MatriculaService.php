<?php

namespace App\Services;

use App\Models\AlunoModel;
use App\Models\MatriculaModel;
use App\Models\ResponsavelLegalModel;
use App\Models\TurmaModel;

/**
 * Fluxos de matrícula: matricular, trocar de turma e cancelar (RF07, RF08, RF09, HU03).
 *
 * Centraliza as regras de vaga (RN01, RN02), situação da turma (RN10), menor de idade (RN04)
 * e duplicidade, para que matrícula e troca de turma apliquem exatamente as mesmas checagens.
 */
class MatriculaService
{
    public function __construct(
        private readonly MatriculaModel $matriculaModel = new MatriculaModel(),
        private readonly AlunoModel $alunoModel = new AlunoModel(),
        private readonly TurmaModel $turmaModel = new TurmaModel(),
        private readonly ResponsavelLegalModel $responsavelModel = new ResponsavelLegalModel(),
    ) {
    }

    /**
     * Matricula o aluno em uma turma, respeitando vagas, situação e duplicidade (RF07, RF08).
     *
     * @return array{sucesso: bool, mensagem: string}
     */
    public function matricular(int $alunoId, int $turmaId): array
    {
        $banco = db_connect();
        $banco->transStart();
        $this->travarTurma($turmaId);

        $erro = $this->conferirRegras($alunoId, $turmaId);
        if ($erro === null) {
            $this->matriculaModel->insert([
                'aluno_id'       => $alunoId,
                'turma_id'       => $turmaId,
                'data_matricula' => date('Y-m-d'),
                'situacao'       => 'ATIVA',
            ]);
        }

        $banco->transComplete();

        if ($erro !== null) {
            return ['sucesso' => false, 'mensagem' => $erro];
        }

        return $banco->transStatus()
            ? ['sucesso' => true, 'mensagem' => 'Matrícula realizada com sucesso.']
            : ['sucesso' => false, 'mensagem' => 'Não foi possível concluir a matrícula. Tente novamente.'];
    }

    /**
     * Troca o aluno de turma: a matrícula antiga fica TRANSFERIDA e nasce uma nova ATIVA (RF09).
     *
     * A troca valida a turma de destino com as mesmas regras de uma matrícula nova. A turma
     * antiga mantém o histórico em vez de ser apagada.
     *
     * @return array{sucesso: bool, mensagem: string}
     */
    public function trocarTurma(int $matriculaId, int $novaTurmaId): array
    {
        $matricula = $this->matriculaModel->find($matriculaId);
        if ($matricula === null || $matricula['situacao'] !== 'ATIVA') {
            return ['sucesso' => false, 'mensagem' => 'Só é possível trocar a turma de uma matrícula ativa.'];
        }

        if ((int) $matricula['turma_id'] === $novaTurmaId) {
            return ['sucesso' => false, 'mensagem' => 'Escolha uma turma diferente da atual.'];
        }

        $banco = db_connect();
        $banco->transStart();
        $this->travarTurma($novaTurmaId);

        $erro = $this->conferirRegras((int) $matricula['aluno_id'], $novaTurmaId);
        if ($erro === null) {
            $hoje = date('Y-m-d');
            $this->matriculaModel->update($matriculaId, ['situacao' => 'TRANSFERIDA', 'data_encerramento' => $hoje]);
            $this->matriculaModel->insert([
                'aluno_id'       => $matricula['aluno_id'],
                'turma_id'       => $novaTurmaId,
                'data_matricula' => $hoje,
                'situacao'       => 'ATIVA',
            ]);
        }

        $banco->transComplete();

        if ($erro !== null) {
            return ['sucesso' => false, 'mensagem' => $erro];
        }

        return $banco->transStatus()
            ? ['sucesso' => true, 'mensagem' => 'Troca de turma realizada com sucesso.']
            : ['sucesso' => false, 'mensagem' => 'Não foi possível trocar de turma. Tente novamente.'];
    }

    /**
     * Cancela uma matrícula ativa, liberando a vaga e mantendo o registro no histórico.
     *
     * @return array{sucesso: bool, mensagem: string}
     */
    public function cancelar(int $matriculaId): array
    {
        $matricula = $this->matriculaModel->find($matriculaId);
        if ($matricula === null || $matricula['situacao'] !== 'ATIVA') {
            return ['sucesso' => false, 'mensagem' => 'Só é possível cancelar uma matrícula ativa.'];
        }

        $this->matriculaModel->update($matriculaId, ['situacao' => 'CANCELADA', 'data_encerramento' => date('Y-m-d')]);

        return ['sucesso' => true, 'mensagem' => 'Matrícula cancelada.'];
    }

    /**
     * Confere todas as regras para colocar o aluno na turma; devolve a mensagem do primeiro problema.
     *
     * Deve rodar depois de travarTurma(), para a contagem de vagas não ficar desatualizada
     * quando duas recepcionistas matriculam ao mesmo tempo.
     */
    private function conferirRegras(int $alunoId, int $turmaId): ?string
    {
        $aluno = $this->alunoModel->find($alunoId);
        $turma = $this->turmaModel->buscarDetalhe($turmaId);

        if ($aluno === null || $turma === null) {
            return 'Aluno ou turma não encontrados.';
        }

        if ($aluno['situacao'] !== 'ATIVO') {
            return 'Aluno inativo não pode ser matriculado. Reative o cadastro primeiro.';
        }

        // RN10: turma suspensa ou encerrada não recebe novas matrículas.
        if ($turma['situacao'] !== 'ATIVA') {
            return 'A turma está ' . strtolower($turma['situacao']) . ' e não aceita novas matrículas.';
        }

        // RN04: menor de idade só entra com responsável legal cadastrado.
        if ($this->alunoModel->ehMenorDeIdade($aluno['data_nascimento'])
            && $this->responsavelModel->buscarPorAluno($alunoId) === null) {
            return 'Aluno menor de idade precisa de responsável legal cadastrado antes da matrícula.';
        }

        if ($this->matriculaModel->possuiMatriculaAtiva($alunoId, $turmaId)) {
            return 'O aluno já está matriculado nesta turma.';
        }

        // RN01/RN02: o limite da turma nunca passa da capacidade da modalidade (lutas 20, coletivos 25).
        if ((int) $turma['ocupadas'] >= (int) $turma['limite_vagas']) {
            return 'A turma atingiu o limite de ' . $turma['limite_vagas'] . ' vagas.';
        }

        return null;
    }

    /**
     * Trava a linha da turma até o fim da transação, serializando matrículas concorrentes.
     */
    private function travarTurma(int $turmaId): void
    {
        $this->turmaModel->travarParaMatricula($turmaId);
    }
}
