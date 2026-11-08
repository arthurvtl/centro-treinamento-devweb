<?php

namespace App\Database\Seeds;

/**
 * Gera CPFs matematicamente válidos para os dados de demonstração.
 */
class CpfDemonstracao
{
    /**
     * Completa 9 dígitos-base com os dois dígitos verificadores.
     */
    public static function gerar(string $base): string
    {
        $cpf = $base;

        for ($posicao = 9; $posicao < 11; $posicao++) {
            $soma = 0;
            for ($indice = 0; $indice < $posicao; $indice++) {
                $soma += (int) $cpf[$indice] * (($posicao + 1) - $indice);
            }
            $cpf .= ((10 * $soma) % 11) % 10;
        }

        return $cpf;
    }
}
