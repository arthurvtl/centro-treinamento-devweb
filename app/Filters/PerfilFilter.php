<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Restringe rotas a determinados perfis (RF21, RN05, HU07).
 *
 * Uso na rota: `['filter' => 'perfil:proprietario']`. Perfis aceitos: proprietario | recepcionista.
 */
class PerfilFilter implements FilterInterface
{
    /**
     * Nega o acesso (403) quando o perfil da sessão não está entre os permitidos.
     *
     * Responde 403 em vez de redirecionar para deixar claro que a tela existe, mas o
     * usuário não tem permissão (ex.: recepcionista tentando o relatório financeiro, RN05).
     *
     * @param list<string>|null $arguments perfis permitidos informados na rota
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $perfilAtual = session()->get('usuario_perfil');

        if (! in_array($perfilAtual, $arguments ?? [], true)) {
            return service('response')
                ->setStatusCode(403)
                ->setBody(view('errors/acesso_negado'));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
