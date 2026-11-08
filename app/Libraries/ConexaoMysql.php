<?php

namespace App\Libraries;

use Config\Database;
use RuntimeException;

/**
 * Prepara a chamada dos clientes `mysqldump` e `mysql` a partir da configuração do banco (.env).
 *
 * Usada pelos comandos de backup e restauração (RNF03). As credenciais continuam só no `.env`.
 * Elas seguem para o cliente por um arquivo de opções temporário, legível só pelo dono, e não
 * pela linha de comando: assim a senha não aparece em `ps` para outros usuários da máquina (RNF06).
 *
 * Por que `--defaults-file` e não `MYSQL_PWD`: o `--defaults-file` faz o cliente ignorar outros
 * arquivos de configuração da máquina (como o `~/.my.cnf`), que de outra forma sobrepõem a
 * senha do `.env` e causam "Access denied".
 */
class ConexaoMysql
{
    /** @var array<string, mixed> */
    private array $configuracao;

    public function __construct()
    {
        $this->configuracao = (array) config(Database::class)->default;
    }

    /**
     * Nome do banco configurado no .env.
     */
    public function nomeDoBanco(): string
    {
        return (string) $this->configuracao['database'];
    }

    /**
     * Caminho do executável `mysqldump`; pode ser trocado no .env com `backup.mysqldump`.
     */
    public function binarioDump(): string
    {
        return (string) env('backup.mysqldump', 'mysqldump');
    }

    /**
     * Caminho do executável `mysql`; pode ser trocado no .env com `backup.mysql`.
     */
    public function binarioCliente(): string
    {
        return (string) env('backup.mysql', 'mysql');
    }

    /**
     * Cria o arquivo de opções temporário com servidor, porta, usuário e senha do .env.
     *
     * O arquivo nasce com permissão 0600 antes de receber a senha. Quem chamou é responsável
     * por apagá-lo com removerArquivoDeOpcoes() assim que o cliente terminar.
     *
     * @return string caminho do arquivo criado
     */
    public function criarArquivoDeOpcoes(): string
    {
        $pasta = WRITEPATH . 'backups';
        if (! is_dir($pasta) && ! mkdir($pasta, 0750, true)) {
            throw new RuntimeException('Não foi possível criar a pasta ' . $pasta);
        }

        $arquivo = tempnam($pasta, '.opcoes');
        chmod($arquivo, 0600);

        $conteudo = "[client]\n"
            . 'host=' . $this->configuracao['hostname'] . "\n"
            . 'port=' . ($this->configuracao['port'] ?: 3306) . "\n"
            . 'user=' . $this->configuracao['username'] . "\n"
            . 'password="' . addcslashes((string) $this->configuracao['password'], '\\"') . "\"\n";
        file_put_contents($arquivo, $conteudo);

        return $arquivo;
    }

    /**
     * Devolve as opções extras do mysqldump que o cliente instalado reconhece.
     *
     * Versões e servidores diferentes (MySQL 8, 9, MariaDB) aceitam opções diferentes, e uma
     * opção desconhecida derruba o backup. Por isso cada opção só entra se aparecer no `--help`:
     * - `--no-tablespaces`: não exige o privilégio PROCESS, que o usuário da aplicação não tem;
     * - `--set-gtid-purged=OFF`: evita o FLUSH TABLES (privilégio RELOAD) em servidor com GTID e
     *   deixa o arquivo restaurável sem privilégios de administrador;
     * - `--skip-masking-policies`: evita ler tabela de sistema que o usuário da aplicação não acessa.
     *
     * @return list<string>
     */
    public function opcoesOpcionaisDoDump(): array
    {
        $ajuda = $this->lerAjuda($this->binarioDump());

        $candidatas = [
            '--no-tablespaces'       => '--no-tablespaces',
            '--set-gtid-purged'      => '--set-gtid-purged=OFF',
            '--masking-policies'     => '--skip-masking-policies',
        ];

        $suportadas = [];
        foreach ($candidatas as $opcaoNoAjuda => $opcaoUsada) {
            if (str_contains($ajuda, $opcaoNoAjuda)) {
                $suportadas[] = $opcaoUsada;
            }
        }

        return $suportadas;
    }

    /**
     * Apaga o arquivo de opções temporário.
     */
    public function removerArquivoDeOpcoes(string $arquivo): void
    {
        is_file($arquivo) && unlink($arquivo);
    }

    /**
     * Opções que vão no começo da chamada do cliente (`--defaults-file` precisa ser a primeira).
     *
     * @return list<string>
     */
    public function opcoesDeConexao(string $arquivoDeOpcoes): array
    {
        return ['--defaults-file=' . $arquivoDeOpcoes, '--default-character-set=utf8mb4'];
    }

    /**
     * Lê o texto de `--help` de um executável, sem passar por shell. Vazio se não for possível rodar.
     */
    private function lerAjuda(string $binario): string
    {
        $processo = @proc_open([$binario, '--help'], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $canais);

        if (! is_resource($processo)) {
            return '';
        }

        $texto = (string) stream_get_contents($canais[1]);
        fclose($canais[1]);
        fclose($canais[2]);
        proc_close($processo);

        return $texto;
    }
}
