<?php

/**
 * Usado só na inicialização do contêiner: termina com código 0 se a tabela de usuários
 * estiver vazia (banco recém-criado, precisa dos seeders) e 1 caso contrário.
 * Lê as mesmas variáveis de ambiente que o CodeIgniter usa para conectar.
 */
$certificadoCa = getenv('DB_SSL_CA') ?: null;
$conexao       = mysqli_init();

if ($certificadoCa !== null) {
    $conexao->ssl_set(null, null, $certificadoCa, null, null);
}

$conectou = $conexao->real_connect(
    getenv('database_default_hostname'),
    getenv('database_default_username'),
    getenv('database_default_password'),
    getenv('database_default_database'),
    (int) (getenv('database_default_port') ?: 3306),
    null,
    $certificadoCa !== null ? MYSQLI_CLIENT_SSL : 0,
);

if (! $conectou) {
    fwrite(STDERR, "Não foi possível conectar ao banco.\n");
    exit(2);
}

$resultado = $conexao->query('SELECT COUNT(*) FROM usuarios');
exit((int) $resultado->fetch_row()[0] === 0 ? 0 : 1);
