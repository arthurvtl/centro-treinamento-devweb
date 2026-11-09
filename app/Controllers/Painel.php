<?php

namespace App\Controllers;

use App\Services\IndicadoresService;

/**
 * Página inicial da área logada.
 */
class Painel extends BaseController
{
    /**
     * Mostra os indicadores e os atalhos dos módulos conforme o perfil do usuário.
     */
    public function index()
    {
        return view('painel/index', [
            'titulo'      => 'Painel',
            'indicadores' => (new IndicadoresService())->doPainel((string) session()->get('usuario_perfil')),
        ]);
    }
}
