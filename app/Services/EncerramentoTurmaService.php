<?php

namespace App\Services;

use App\Models\AlunoModel;
use App\Models\FrequenciaModel;
use App\Models\MatriculaModel;
use App\Models\TurmaModel;

/**
 * Encerramento de turma: finaliza matrículas, apura frequência, emite certificados e
 * inativa alunos sem outra turma ativa (RF23, RN09, HU03).
 */
class EncerramentoTurmaService
{
    public function __construct(
        private readonly TurmaModel $turmaModel = new TurmaModel(),
        private readonly MatriculaModel $matriculaModel = new MatriculaModel(),
        private readonly FrequenciaModel $frequenciaModel = new FrequenciaModel(),
        private readonly AlunoModel $alunoModel = new AlunoModel(),
    ) {
    }

    /**
     * Encerra a turma e executa a apuração da RN09 numa única transação.
     *
     * Para cada matrícula ativa: vira FINALIZADA; o certificado só é emitido com pelo menos 75%
     * de presença nas aulas lançadas (quem não teve nenhuma aula lançada não tem como provar
     * frequência, então não recebe). Depois, o aluno que ficou sem nenhuma outra matrícula ativa
     * é inativado. Tudo ou nada: uma falha no meio não deixa a turma meio encerrada.
     *
     * @return array{sucesso: bool, mensagem: string}
     */
    public function encerrar(int $turmaId): array
    {
        $turma = $this->turmaModel->find($turmaId);

        if ($turma === null) {
            return ['sucesso' => false, 'mensagem' => 'Turma não encontrada.'];
        }

        if ($turma['situacao'] === 'ENCERRADA') {
            return ['sucesso' => false, 'mensagem' => 'Esta turma já está encerrada.'];
        }

        $hoje       = date('Y-m-d');
        $resumos    = $this->frequenciaModel->resumirPresencaDaTurma($turmaId);
        $certificados = 0;
        $inativados   = [];

        $banco = db_connect();
        $banco->transStart();

        foreach ($resumos as $resumo) {
            $mereceCertificado = $resumo['percentual'] !== null && $resumo['percentual'] >= FrequenciaModel::FREQUENCIA_MINIMA;
            $certificados     += $mereceCertificado ? 1 : 0;

            $this->matriculaModel->update($resumo['matricula_id'], [
                'situacao'            => 'FINALIZADA',
                'data_encerramento'   => $hoje,
                'certificado_emitido' => $mereceCertificado ? 1 : 0,
            ]);
        }

        $this->turmaModel->update($turmaId, [
            'situacao'     => 'ENCERRADA',
            'data_termino' => $turma['data_termino'] ?: $hoje,
        ]);

        // Só depois de finalizar as matrículas desta turma dá para saber quem ficou sem outra turma ativa.
        foreach ($resumos as $resumo) {
            $alunoId = (int) $resumo['aluno_id'];
            if (! $this->matriculaModel->where('aluno_id', $alunoId)->where('situacao', 'ATIVA')->countAllResults()) {
                $this->alunoModel->update($alunoId, ['situacao' => 'INATIVO']);
                $inativados[] = $alunoId;
            }
        }

        $banco->transComplete();

        if (! $banco->transStatus()) {
            return ['sucesso' => false, 'mensagem' => 'Não foi possível encerrar a turma. Nada foi alterado.'];
        }

        return ['sucesso' => true, 'mensagem' => sprintf(
            'Turma encerrada: %d matrícula(s) finalizada(s), %d certificado(s) emitido(s) e %d aluno(s) inativado(s).',
            count($resumos),
            $certificados,
            count($inativados),
        )];
    }
}
