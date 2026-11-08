<?php

namespace App\Commands;

use App\Libraries\ConexaoMysql;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Restaura o banco a partir de um backup: `php spark backup:restaurar <arquivo>` (RNF03).
 *
 * A restauração SUBSTITUI as tabelas atuais pelas do arquivo, por isso exige confirmação
 * digitando o nome do banco. Aceita o caminho completo ou só o nome de um arquivo da pasta
 * `writable/backups`. Para automação, `--forcar` dispensa a pergunta.
 */
class RestaurarBackup extends BaseCommand
{
    protected $group       = 'Backup';
    protected $name        = 'backup:restaurar';
    protected $description = 'Restaura o banco a partir de um backup .sql.gz, substituindo os dados atuais (pede confirmação).';
    protected $usage       = 'backup:restaurar <arquivo> [--forcar]';
    protected $arguments   = [
        'arquivo' => 'Caminho do backup (.sql.gz) ou nome de um arquivo em writable/backups.',
    ];
    protected $options = [
        '--forcar' => 'Pula a confirmação (use só em scripts).',
    ];

    /**
     * Valida o arquivo, confirma com o usuário e envia o conteúdo para o cliente mysql.
     *
     * @param list<string> $params o primeiro item é o arquivo de backup
     */
    public function run(array $params): int
    {
        $arquivo = $this->localizarArquivo($params[0] ?? null);
        if ($arquivo === null) {
            CLI::error('Informe um backup existente. Exemplo: php spark backup:restaurar ct_devweb_20261108_0300.sql.gz');
            $this->listarBackups();

            return EXIT_USER_INPUT;
        }

        $conexao = new ConexaoMysql();
        $banco   = $conexao->nomeDoBanco();

        CLI::write('Arquivo: ' . $arquivo, 'yellow');
        CLI::write('Esta operação SUBSTITUI os dados atuais do banco "' . $banco . '" pelos do backup.', 'red');

        if (CLI::getOption('forcar') === null && trim((string) CLI::prompt('Para continuar, digite o nome do banco')) !== $banco) {
            CLI::write('Nome diferente: restauração cancelada. Nada foi alterado.');

            return EXIT_ERROR;
        }

        CLI::write('Restaurando...');
        $erro = $this->enviarAoMysql($conexao, $arquivo);
        if ($erro !== null) {
            CLI::error('Falha ao restaurar: ' . $erro);

            return EXIT_ERROR;
        }

        CLI::write('Banco "' . $banco . '" restaurado com sucesso.', 'green');

        return EXIT_SUCCESS;
    }

    /**
     * Resolve o argumento para um arquivo existente: caminho informado ou nome dentro de writable/backups.
     */
    private function localizarArquivo(?string $argumento): ?string
    {
        if ($argumento === null || $argumento === '') {
            return null;
        }

        foreach ([$argumento, WRITEPATH . 'backups' . DIRECTORY_SEPARATOR . basename($argumento)] as $candidato) {
            if (is_file($candidato)) {
                return realpath($candidato);
            }
        }

        return null;
    }

    /**
     * Mostra os backups disponíveis, do mais recente ao mais antigo, para o usuário escolher.
     */
    private function listarBackups(): void
    {
        $arquivos = glob(WRITEPATH . 'backups' . DIRECTORY_SEPARATOR . '*.sql.gz') ?: [];
        rsort($arquivos);

        if ($arquivos === []) {
            CLI::write('Nenhum backup encontrado em writable/backups.');

            return;
        }

        CLI::write('Backups disponíveis:');
        foreach ($arquivos as $arquivo) {
            CLI::write('  ' . basename($arquivo));
        }
    }

    /**
     * Descompacta o backup aos poucos e entrega ao `mysql` pela entrada padrão.
     *
     * @return string|null mensagem de erro, ou null se deu certo
     */
    private function enviarAoMysql(ConexaoMysql $conexao, string $arquivo): ?string
    {
        $leitura = @gzopen($arquivo, 'rb');
        if ($leitura === false) {
            return 'não foi possível abrir o arquivo (ele é mesmo um .sql.gz?).';
        }

        $arquivoDeOpcoes = $conexao->criarArquivoDeOpcoes();
        $comando         = array_merge([$conexao->binarioCliente()], $conexao->opcoesDeConexao($arquivoDeOpcoes), [$conexao->nomeDoBanco()]);
        $arquivoErros = WRITEPATH . 'backups' . DIRECTORY_SEPARATOR . '.erros.tmp';

        $processo = @proc_open(
            $comando,
            [0 => ['pipe', 'r'], 2 => ['file', $arquivoErros, 'w']],
            $canais,
        );

        if (! is_resource($processo)) {
            gzclose($leitura);
            $conexao->removerArquivoDeOpcoes($arquivoDeOpcoes);

            return 'não foi possível executar "' . $conexao->binarioCliente() . '". Informe o caminho em backup.mysql no .env.';
        }

        while (! gzeof($leitura)) {
            fwrite($canais[0], (string) gzread($leitura, 65536));
        }
        gzclose($leitura);
        fclose($canais[0]);

        $codigo = proc_close($processo);
        $conexao->removerArquivoDeOpcoes($arquivoDeOpcoes);
        $erros = trim((string) @file_get_contents($arquivoErros));
        @unlink($arquivoErros);

        return $codigo === 0 ? null : ($erros !== '' ? $erros : "o mysql terminou com código {$codigo}.");
    }
}
