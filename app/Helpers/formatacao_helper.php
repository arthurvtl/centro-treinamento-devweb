<?php

/**
 * Funções de formatação para exibição nas Views (CPF, telefone e data no padrão brasileiro).
 *
 * O banco guarda CPF e telefone só com dígitos e datas em Y-m-d; a máscara é apenas visual.
 */
if (! function_exists('formatar_cpf')) {
    /**
     * Formata 11 dígitos como 000.000.000-00; devolve o valor original se o tamanho não bater.
     */
    function formatar_cpf(?string $cpf): string
    {
        $digitos = preg_replace('/\D/', '', (string) $cpf);

        return strlen($digitos) === 11
            ? substr($digitos, 0, 3) . '.' . substr($digitos, 3, 3) . '.' . substr($digitos, 6, 3) . '-' . substr($digitos, 9)
            : (string) $cpf;
    }
}

if (! function_exists('formatar_telefone')) {
    /**
     * Formata telefone com DDD, no padrão (00) 00000-0000 ou (00) 0000-0000.
     */
    function formatar_telefone(?string $telefone): string
    {
        $digitos = preg_replace('/\D/', '', (string) $telefone);

        if (strlen($digitos) === 11) {
            return sprintf('(%s) %s-%s', substr($digitos, 0, 2), substr($digitos, 2, 5), substr($digitos, 7));
        }

        if (strlen($digitos) === 10) {
            return sprintf('(%s) %s-%s', substr($digitos, 0, 2), substr($digitos, 2, 4), substr($digitos, 6));
        }

        return (string) $telefone;
    }
}

if (! function_exists('formatar_data')) {
    /**
     * Converte uma data Y-m-d para dd/mm/aaaa; devolve traço quando vazia.
     */
    function formatar_data(?string $data): string
    {
        return ($data === null || $data === '') ? '—' : date('d/m/Y', strtotime($data));
    }
}

if (! function_exists('formatar_moeda')) {
    /**
     * Formata um valor decimal como moeda brasileira, ex.: R$ 1.250,50.
     */
    function formatar_moeda(string|float|int|null $valor): string
    {
        return 'R$ ' . number_format((float) $valor, 2, ',', '.');
    }
}
