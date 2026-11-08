<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Classe base de todos os Controllers do Sistema CT.
 *
 * Concentra o que todos precisam: os helpers usados pelas Views (formulários, URLs, menu por
 * perfil e formatação de CPF, telefone, data e moeda).
 */
abstract class BaseController extends Controller
{
    /**
     * Helpers carregados em todos os Controllers.
     *
     * @var list<string>
     */
    protected $helpers = ['form', 'url', 'menu', 'formatacao'];

    /**
     * Prepara o Controller para atender a requisição.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
    }
}
