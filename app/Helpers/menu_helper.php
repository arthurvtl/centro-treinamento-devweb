<?php

/**
 * Itens da navbar por perfil (RF21, RN05).
 *
 * A navbar só esconde links; quem barra o acesso de fato são os filtros de rota.
 */
if (! function_exists('itens_menu')) {
    /**
     * Lista os links de navegação que o perfil informado pode ver.
     *
     * @return list<array{rota: string, rotulo: string}>
     */
    function itens_menu(?string $perfil): array
    {
        $itens = [
            ['rota' => 'alunos',      'rotulo' => 'Alunos'],
            ['rota' => 'turmas',      'rotulo' => 'Turmas'],
            ['rota' => 'matriculas',  'rotulo' => 'Matrículas'],
            ['rota' => 'frequencias', 'rotulo' => 'Frequência'],
            ['rota' => 'relatorios',  'rotulo' => 'Relatórios'],
        ];

        if ($perfil === 'proprietario') {
            $itens[] = ['rota' => 'professores',        'rotulo' => 'Professores'];
            $itens[] = ['rota' => 'modalidades',        'rotulo' => 'Modalidades'];
            $itens[] = ['rota' => 'planos',             'rotulo' => 'Planos'];
            $itens[] = ['rota' => 'mensalidades',       'rotulo' => 'Mensalidades'];
        }

        return $itens;
    }
}
