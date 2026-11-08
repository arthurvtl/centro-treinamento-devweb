<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Exige sessão autenticada para acessar áreas restritas (RF20, HU07).
 *
 * Aplicado ao grupo de rotas protegidas; quem não está logado vai para o login.
 */
class AuthFilter implements FilterInterface
{
    /**
     * Redireciona para o login quando não há usuário na sessão.
     *
     * A verificação acontece no servidor, não só pelo esconder de botões (RNF05).
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('usuario_id')) {
            return redirect()->to(site_url('login'))
                ->with('erro', 'Faça login para acessar o sistema.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
