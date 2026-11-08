<?php

namespace App\Services;

use App\Models\FrequenciaModel;
use App\Models\MatriculaModel;
use App\Models\TurmaModel;

/**
 * Registro de chamada por turma e data (RF10, RN10, HU04).
 */
class FrequenciaService
{
    public function __construct(
        private readonly FrequenciaModel $frequenciaModel = new FrequenciaModel(),
        private readonly MatriculaModel $matriculaModel = new MatriculaModel(),
        private readonly TurmaModel $turmaModel = new TurmaModel(),
    ) {
    }

    /**
     * Registra a chamada inteira de uma turma numa data, tudo ou nada (RF10).
     *
     * Barra turma suspensa ou encerrada (RN10). Se o aluno já tem lançamento naquela data,
     * o registro é corrigido em vez de duplicado, respeitando a restrição única
     * (matrícula, data) do banco.
     *
     * @param array<int|string, string> $presencas   situação por matrícula: [matricula_id => PRESENTE|FALTA]
     * @param array<int|string, string> $observacoes observação opcional por matrícula
     * @return array{sucesso: bool, mensagem: string}
     */
    public function registrarChamada(int $turmaId, string $dataAula, array $presencas, array $observacoes = []): array
    {
        helper('formatacao');

        $turma = $this->turmaModel->find($turmaId);
        if ($turma === null) {
            return ['sucesso' => false, 'mensagem' => 'Turma não encontrada.'];
        }

        // RN10: turma suspensa ou encerrada não recebe lançamentos de frequência.
        if ($turma['situacao'] !== 'ATIVA') {
            return ['sucesso' => false, 'mensagem' => 'A turma está ' . strtolower($turma['situacao']) . ' e não aceita lançamento de frequência.'];
        }

        $matriculasAtivas = array_column($this->matriculaModel->buscarAlunosDaTurma($turmaId), 'id');
        if ($matriculasAtivas === []) {
            return ['sucesso' => false, 'mensagem' => 'A turma não tem alunos matriculados.'];
        }

        $lancamentos = [];
        foreach ($matriculasAtivas as $matriculaId) {
            $lancamento = [
                'matricula_id' => (int) $matriculaId,
                'data_aula'    => $dataAula,
                'situacao'     => $presencas[$matriculaId] ?? '',
                'observacao'   => trim((string) ($observacoes[$matriculaId] ?? '')) ?: null,
            ];

            if (! $this->frequenciaModel->validate($lancamento)) {
                return ['sucesso' => false, 'mensagem' => implode(' ', array_unique($this->frequenciaModel->errors()))];
            }

            $lancamentos[] = $lancamento;
        }

        $jaLancados = $this->frequenciaModel->buscarLancamentosDaAula($turmaId, $dataAula);

        $banco = db_connect();
        $banco->transStart();
        foreach ($lancamentos as $lancamento) {
            $existente = $jaLancados[$lancamento['matricula_id']] ?? null;
            $existente === null
                ? $this->frequenciaModel->insert($lancamento)
                : $this->frequenciaModel->update($existente['id'], $lancamento);
        }
        $banco->transComplete();

        return $banco->transStatus()
            ? ['sucesso' => true, 'mensagem' => 'Frequência de ' . formatar_data($dataAula) . ' registrada.']
            : ['sucesso' => false, 'mensagem' => 'Não foi possível registrar a frequência. Tente novamente.'];
    }
}
