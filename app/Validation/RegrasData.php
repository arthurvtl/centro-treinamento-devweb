<?php

namespace App\Validation;

/**
 * Regras de validação de data do Sistema CT.
 *
 * Registrada em Config\Validation::$ruleSets para ser usada nas regras dos Models.
 */
class RegrasData
{
    /**
     * Garante que a data (Y-m-d) não está no futuro.
     *
     * Usada na data de nascimento: ninguém nasce amanhã, e uma data futura
     * quebraria o cálculo de idade usado na regra do menor de idade (RN04).
     */
    public function data_nao_futura(?string $valor = null): bool
    {
        if ($valor === null || $valor === '') {
            return false;
        }

        return $valor <= date('Y-m-d');
    }
}
