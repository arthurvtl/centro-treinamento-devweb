<?php

namespace App\Commands;

use App\Libraries\ConexaoMysql;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Gera o backup compactado do banco de dados: `php spark backup:banco` (RNF03).
 *
 * O arquivo sai em `writable/backups/<banco>_AAAAMMDD_HHMM.sql.gz` e só os últimos N
 * backups são mantidos, para a pasta não crescer sem limite. O backup contém dados
 * pessoais e a condição de saúde dos alunos (dado sensível, LGPD, RNF06): por isso o arquivo
 * fica com permissão só do dono, a pasta não é versionada e a cópia para fora do servidor deve
 * ser feita por canal protegido (veja o README).
 */
class BackupBanco extends BaseCommand
{
    /** Quantidade padrão de backups mantidos: 14 backups diários cobrem duas semanas. */
    private const MANTER_PADRAO = 14;

    protected $group       = 'Backup';
    protected $name        = 'backup:banco';
    protected $description = 'Gera o backup compactado (.sql.gz) do banco em writable/backups e apaga os mais antigos.';
    protected $usage       = 'backup:banco [--manter N]';
    protected $options     = [
        '--manter' => 'Quantos backups manter na pasta (padrão: 14). Os mais antigos são apagados.',
    ];

    /**
     * Executa o mysqldump, grava o arquivo compactado e aplica a retenção.
     *
     * @param list<string> $params argumentos do comando (não usados)
     */
    public function run(array $params): int
    {
        $manter = (int) (CLI::getOption('manter') ?? self::MANTER_PADRAO);
        if ($manter < 1) {
            CLI::error('A opção --manter precisa ser um número maior que zero.');

            return EXIT_USER_INPUT;
        }

        $conexao = new ConexaoMysql();
        $pasta   = WRITEPATH . 'backups' . DIRECTORY_SEPARATOR;
        if (! is_dir($pasta) && ! mkdir($pasta, 0750, true)) {
            CLI::error('Não foi possível criar a pasta de backups: ' . $pasta);

            return EXIT_ERROR;
        }

        $destino = $pasta . $conexao->nomeDoBanco() . '_' . date('Ymd_Hi') . '.sql.gz';
        CLI::write('Gerando backup do banco "' . $conexao->nomeDoBanco() . '"...');

        $erro = $this->despejarBanco($conexao, $destino);
        if ($erro !== null) {
            // Um arquivo parcial seria confundido com um backup bom na hora de restaurar.
            is_file($destino) && unlink($destino);
            CLI::error('Falha ao gerar o backup: ' . $erro);

            return EXIT_ERROR;
        }

        chmod($destino, 0600);
        CLI::write('Backup criado: ' . $destino . ' (' . $this->formatarTamanho(filesize($destino)) . ')', 'green');

        $apagados = $this->aplicarRetencao($pasta, $conexao->nomeDoBanco(), $manter);
        if ($apagados > 0) {
            CLI::write("Retenção: {$apagados} backup(s) antigo(s) apagado(s); mantidos os últimos {$manter}.");
        }

        CLI::write('Lembrete: copie o arquivo para fora do servidor (ele contém dados pessoais e de saúde).', 'yellow');

        return EXIT_SUCCESS;
    }

    /**
     * Roda o mysqldump e grava a saída já compactada no arquivo de destino.
     *
     * Sem shell: o comando é passado como lista de argumentos (sem risco de injeção) e a
     * saída é compactada em memória, sem arquivo temporário com dados em texto puro. A senha vai
     * num arquivo de opções temporário (0600), apagado ao final, e não na linha de comando.
     * `--single-transaction` garante um retrato consistente sem travar o sistema em uso.
     *
     * @return string|null mensagem de erro, ou null se deu certo
     */
    private function despejarBanco(ConexaoMysql $conexao, string $destino): ?string
    {
        $arquivoDeOpcoes = $conexao->criarArquivoDeOpcoes();
        $comando         = array_merge(
            [$conexao->binarioDump()],
            $conexao->opcoesDeConexao($arquivoDeOpcoes),
            ['--single-transaction', '--routines', '--triggers'],
            $conexao->opcoesOpcionaisDoDump(),
            [$conexao->nomeDoBanco()],
        );

        $arquivoErros = WRITEPATH . 'backups' . DIRECTORY_SEPARATOR . '.erros.tmp';
        $processo     = @proc_open(
            $comando,
            [1 => ['pipe', 'w'], 2 => ['file', $arquivoErros, 'w']],
            $canais,
        );

        if (! is_resource($processo)) {
            $conexao->removerArquivoDeOpcoes($arquivoDeOpcoes);

            return 'não foi possível executar "' . $conexao->binarioDump() . '". Ele está instalado? Informe o caminho em backup.mysqldump no .env.';
        }

        $saida = gzopen($destino, 'wb9');
        while (! feof($canais[1])) {
            gzwrite($saida, (string) fread($canais[1], 65536));
        }
        gzclose($saida);
        fclose($canais[1]);

        $codigo = proc_close($processo);
        $conexao->removerArquivoDeOpcoes($arquivoDeOpcoes);
        $erros = trim((string) @file_get_contents($arquivoErros));
        @unlink($arquivoErros);

        return $codigo === 0 ? null : ($erros !== '' ? $erros : "o mysqldump terminou com código {$codigo}.");
    }

    /**
     * Apaga os backups mais antigos, mantendo só os `$manter` mais recentes.
     *
     * A data está no nome do arquivo (AAAAMMDD_HHMM), então ordenar pelo nome é ordenar pela data.
     */
    private function aplicarRetencao(string $pasta, string $banco, int $manter): int
    {
        $arquivos = glob($pasta . $banco . '_*.sql.gz') ?: [];
        rsort($arquivos);

        $excedentes = array_slice($arquivos, $manter);
        foreach ($excedentes as $arquivo) {
            unlink($arquivo);
        }

        return count($excedentes);
    }

    /**
     * Mostra o tamanho do arquivo em KB ou MB.
     */
    private function formatarTamanho(int|false $bytes): string
    {
        $bytes = (int) $bytes;

        return $bytes >= 1048576
            ? number_format($bytes / 1048576, 1, ',', '.') . ' MB'
            : number_format($bytes / 1024, 1, ',', '.') . ' KB';
    }
}
