<?php

namespace App\Controllers;

/**
 * Página inicial da área logada.
 */
class Painel extends BaseController
{
    /**
     * Mostra os atalhos dos módulos conforme o perfil do usuário.
     */
    public function index()
    {
        return view('painel/index');
    }
}
