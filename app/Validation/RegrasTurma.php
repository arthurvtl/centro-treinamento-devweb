<?php

namespace App\Validation;

use App\Models\ModalidadeModel;
use App\Models\ProfessorModel;

/**
 * Regras de validação do cadastro de turmas (RF06, RN01, RN02, RN03).
 *
 * Registrada em Config\Validation::$ruleSets para uso nas regras do TurmaModel.
 */
class RegrasTurma
{
    /**
     * Confere se o professor está habilitado na modalidade da turma (RN03).
     *
     * @param array<string, mixed> $dados campos validados, para ler a modalidade escolhida
     */
    public function professor_habilitado(?string $valor, string $campoModalidade, array $dados, ?string &$erro = null): bool
    {
        $modalidadeId = (int) ($dados[$campoModalidade] ?? 0);

        // Id inexistente já é reprovado por is_not_unique; aqui não duplicamos o erro.
        if (! is_numeric($valor) || $modalidadeId === 0) {
            return true;
        }

        if (! (new ProfessorModel())->estaHabilitado((int) $valor, $modalidadeId)) {
            $erro = 'Este professor não está habilitado para a modalidade escolhida.';

            return false;
        }

        return true;
    }

    /**
     * Impede limite de vagas acima da capacidade padrão da modalidade (RN01 e RN02).
     *
     * @param array<string, mixed> $dados
     */
    public function limite_dentro_da_capacidade(?string $valor, string $campoModalidade, array $dados, ?string &$erro = null): bool
    {
        $modalidade = (new ModalidadeModel())->find((int) ($dados[$campoModalidade] ?? 0));

        if ($modalidade === null || ! is_numeric($valor)) {
            return true;
        }

        if ((int) $valor > (int) $modalidade['capacidade_padrao']) {
            $erro = 'O limite de vagas não pode passar de ' . $modalidade['capacidade_padrao'] . ' alunos para esta modalidade.';

            return false;
        }

        return true;
    }

    /**
     * Exige que o horário de término seja depois do início.
     *
     * @param array<string, mixed> $dados
     */
    public function horario_termino_apos_inicio(?string $valor, string $campoInicio, array $dados): bool
    {
        $inicio = $dados[$campoInicio] ?? '';

        return $valor === null || $inicio === '' || strcmp(substr($valor, 0, 5), substr($inicio, 0, 5)) > 0;
    }

    /**
     * Exige que a data de término, quando informada, não seja anterior ao início.
     *
     * @param array<string, mixed> $dados
     */
    public function data_termino_valida(?string $valor, string $campoInicio, array $dados): bool
    {
        $inicio = $dados[$campoInicio] ?? '';

        return $valor === null || $valor === '' || $inicio === '' || $valor >= $inicio;
    }
}
