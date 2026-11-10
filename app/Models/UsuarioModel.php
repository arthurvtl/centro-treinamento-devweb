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
     *
     * O campo `login_email` guarda o identificador de acesso, que pode ser um e-mail ou um usuário
     * simples (ex.: `dono`), por isso não exige formato de e-mail. No login não há tamanho mínimo de
     * senha: quem confere se a senha está certa é o `password_verify`, e uma regra de tamanho aqui
     * só barraria senhas curtas já cadastradas.
     */
    protected $validationRules = [
        'login_email' => 'required|max_length[150]|regex_match[/^[A-Za-z0-9._@-]+$/]',
        'senha'       => 'required|max_length[72]',
    ];

    protected $validationMessages = [
        'login_email' => [
            'required'    => 'Informe o usuário.',
            'max_length'  => 'O usuário deve ter no máximo 150 caracteres.',
            'regex_match' => 'O usuário só pode ter letras, números, ponto, hífen, sublinhado ou @.',
        ],
        'senha' => [
            'required'   => 'Informe a senha.',
            'max_length' => 'A senha deve ter no máximo 72 caracteres.',
        ],
    ];

    /**
     * Confere usuário e senha e devolve o usuário autenticado (RF20, HU07).
     *
     * Retorna null tanto para usuário inexistente quanto para senha errada, de propósito:
     * mensagem única não revela se o usuário está cadastrado. Usuário INATIVO não entra.
     *
     * @param string $identificador conteúdo da coluna `login_email` (e-mail ou usuário simples)
     * @return array<string, mixed>|null
     */
    public function autenticar(string $identificador, string $senha): ?array
    {
        $usuario = $this->where('login_email', $identificador)->where('situacao', 'ATIVO')->first();

        if ($usuario === null || ! password_verify($senha, $usuario['senha_hash'])) {
            return null;
        }

        return $usuario;
    }
}
