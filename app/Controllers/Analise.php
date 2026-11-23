<?php

namespace App\Controllers;

use App\Services\AnaliseService;

/**
 * Tela de análise do negócio, restrita ao proprietário pela rota (HU08, RF29, RN05).
 */
class Analise extends BaseController
{
    /**
     * Mostra indicadores e gráficos de receita, inadimplência, ocupação, retenção, frequência e loja.
     */
    public function index()
    {
        return view('analise/index', ['titulo' => 'Análise do negócio'] + (new AnaliseService())->montar());
    }
}
