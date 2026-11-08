<?php

namespace App\Validation;

/**
 * Regras de validação personalizadas do Sistema CT.
 *
 * Atende RF01/HU01: "O CPF é validado no servidor". Registrada em
 * Config\Validation::$ruleSets, o que permite usar `cpf_valido` nas regras dos Models.
 */
class RegrasCpf
{
    /**
     * Confere se o CPF é matematicamente válido (dois dígitos verificadores).
     *
     * Aceita o valor com ou sem máscara; o Model grava apenas os 11 dígitos.
     */
    public function cpf_valido(?string $valor = null): bool
    {
        $cpf = preg_replace('/\D/', '', (string) $valor);

        // Rejeita tamanho errado e sequências repetidas (111.111.111-11 passa na conta, mas é inválido).
        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        // Calcula cada dígito verificador a partir dos dígitos anteriores.
        for ($posicao = 9; $posicao < 11; $posicao++) {
            $soma = 0;
            for ($indice = 0; $indice < $posicao; $indice++) {
                $soma += (int) $cpf[$indice] * (($posicao + 1) - $indice);
            }
            $digitoEsperado = ((10 * $soma) % 11) % 10;
            if ((int) $cpf[$posicao] !== $digitoEsperado) {
                return false;
            }
        }

        return true;
    }
}
