<?php

namespace App\Controllers;

use App\Models\UsuarioModel;

/**
 * Login e logout do sistema (RF20, RF21, HU07).
 */
class Acesso extends BaseController
{
    /**
     * Exibe o formulário de login; quem já está logado vai direto ao painel.
     */
    public function formularioLogin()
    {
        if (session()->get('usuario_id')) {
            return redirect()->to(site_url('painel'));
        }

        return view('auth/login');
    }

    /**
     * Valida as credenciais e abre a sessão com id, nome e perfil do usuário (RF20, RF21).
     */
    public function entrar()
    {
        $usuarioModel = new UsuarioModel();
        $dados        = $this->request->getPost(['login_email', 'senha']);

        if (! $this->validateData($dados, $usuarioModel->getValidationRules(), $usuarioModel->getValidationMessages())) {
            return redirect()->back()->withInput()->with('erros', $this->validator->getErrors());
        }

        $usuario = $usuarioModel->autenticar($dados['login_email'], $dados['senha']);

        if ($usuario === null) {
            return redirect()->back()->withInput()->with('erro', 'Usuário ou senha inválidos.');
        }

        // Troca o id da sessão no login para impedir fixação de sessão.
        session()->regenerate();
        session()->set([
            'usuario_id'     => $usuario['id'],
            'usuario_nome'   => $usuario['nome'],
            'usuario_perfil' => strtolower($usuario['perfil']),
        ]);

        return redirect()->to(site_url('painel'))->with('sucesso', 'Bem-vindo(a), ' . $usuario['nome'] . '!');
    }

    /**
     * Encerra a sessão, impedindo reutilização da área autenticada (HU07).
     */
    public function sair()
    {
        session()->destroy();

        return redirect()->to(site_url('login'))->with('sucesso', 'Sessão encerrada.');
    }
}
