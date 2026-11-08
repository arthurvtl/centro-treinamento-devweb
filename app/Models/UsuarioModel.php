<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Acesso a dados da tabela `usuarios` (RF20, RF21).
 *
 * Concentra a regra de autenticação para o Controller não conhecer hash nem situação.
 */
class UsuarioModel extends Model
{
    protected $table         = 'usuarios';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['nome', 'login_email', 'senha_hash', 'perfil', 'situacao'];
    protected $useTimestamps = true;

    /**
     * Regras do formulário de login. `senha` não é coluna da tabela: só é validada, nunca gravada.
     */
    protected $validationRules = [
        'login_email' => 'required|valid_email|max_length[150]',
        'senha'       => 'required|min_length[6]',
    ];

    protected $validationMessages = [
        'login_email' => [
            'required'    => 'Informe o e-mail de acesso.',
            'valid_email' => 'Informe um e-mail válido.',
        ],
        'senha' => [
            'required'   => 'Informe a senha.',
            'min_length' => 'A senha deve ter pelo menos 6 caracteres.',
        ],
    ];

    /**
     * Confere e-mail e senha e devolve o usuário autenticado (RF20, HU07).
     *
     * Retorna null tanto para e-mail inexistente quanto para senha errada, de propósito:
     * mensagem única não revela se o e-mail está cadastrado. Usuário INATIVO não entra.
     *
     * @return array<string, mixed>|null
     */
    public function autenticar(string $email, string $senha): ?array
    {
        $usuario = $this->where('login_email', $email)->where('situacao', 'ATIVO')->first();

        if ($usuario === null || ! password_verify($senha, $usuario['senha_hash'])) {
            return null;
        }

        return $usuario;
    }
}
