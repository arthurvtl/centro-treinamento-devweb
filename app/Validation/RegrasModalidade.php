<?php

namespace App\Validation;

/**
 * Regras de validação específicas de modalidades.
 *
 * Registrada em Config\Validation::$ruleSets para uso nas regras do ModalidadeModel.
 */
class RegrasModalidade
{
    /** Capacidade máxima inicial por categoria (RN01 e RN02). INDIVIDUAL não tem teto de negócio definido. */
    private const CAPACIDADE_MAXIMA = [
        'LUTA'             => 20,
        'ESPORTE_COLETIVO' => 25,
        'INDIVIDUAL'       => 100,
    ];

    /**
     * Garante que a capacidade padrão não ultrapassa o teto da categoria (RN01, RN02).
     *
     * Turmas de lutas aceitam até 20 alunos e as de esportes coletivos até 25; barrar já na
     * modalidade evita criar turmas com limite de vagas incoerente.
     *
     * @param array<string, mixed> $dados todos os campos validados, para ler a categoria
     */
    public function capacidade_da_categoria(?string $valor, string $campoCategoria, array $dados, ?string &$erro = null): bool
    {
        $categoria = $dados[$campoCategoria] ?? '';
        $teto      = self::CAPACIDADE_MAXIMA[$categoria] ?? null;

        // Categoria inválida já é reprovada pela regra in_list; aqui não duplicamos o erro.
        if ($teto === null || ! is_numeric($valor)) {
            return true;
        }

        if ((int) $valor > $teto) {
            $erro = 'A capacidade padrão desta categoria não pode passar de ' . $teto . ' alunos.';

            return false;
        }

        return true;
    }
}
