<?php

namespace App\Database\Seeds;

/**
 * Listas e utilitários compartilhados pelos seeders de demonstração.
 *
 * Os dados são fictícios e determinísticos: cada seeder inicia o gerador aleatório com
 * {@see self::SEMENTE}, então a demonstração é sempre igual (as datas são relativas a "hoje",
 * para o sistema parecer em uso no dia da apresentação).
 */
final class DadosDemonstracao
{
    /** Semente fixa do gerador aleatório: mesma semente, mesma demonstração. */
    public const SEMENTE = 20261108;

    // Nomes das turmas: usados pelos seeders para se referir à mesma turma sem depender de ids.
    public const TURMA_JJ_ADULTO     = 'Jiu-jitsu Adulto Noite';
    public const TURMA_JJ_KIDS       = 'Jiu-jitsu Kids Tarde';
    public const TURMA_JJ_INICIANTE  = 'Jiu-jitsu Iniciante Manhã';
    public const TURMA_MT_MANHA      = 'Muay Thai Manhã';
    public const TURMA_MT_NOITE      = 'Muay Thai Noite';
    public const TURMA_BOXE_ADULTO   = 'Boxe Adulto';
    public const TURMA_BOXE_KIDS     = 'Boxe Kids';
    public const TURMA_JUDO_INFANTIL = 'Judô Infantil';
    public const TURMA_JUDO_ADULTO   = 'Judô Adulto';
    public const TURMA_FUTSAL_MISTO  = 'Futsal Misto Sábado';
    public const TURMA_FUTSAL_NOITE  = 'Futsal Adulto Noite';
    public const TURMA_VOLEI         = 'Vôlei Adulto Noite';
    public const TURMA_BASQUETE      = 'Basquete Juvenil';

    public const PRIMEIROS_MASCULINOS = [
        'João', 'Pedro', 'Lucas', 'Gabriel', 'Mateus', 'Rafael', 'Gustavo', 'Felipe', 'Bruno', 'Rodrigo',
        'Thiago', 'Diego', 'Leonardo', 'Vinícius', 'Henrique', 'Eduardo', 'Fernando', 'Marcelo', 'André',
        'Ricardo', 'Daniel', 'Caio', 'Samuel', 'Davi', 'Arthur', 'Miguel', 'Heitor', 'Enzo', 'Otávio',
        'Murilo', 'Renan', 'Leandro',
    ];

    public const PRIMEIROS_FEMININOS = [
        'Maria', 'Ana', 'Beatriz', 'Juliana', 'Camila', 'Larissa', 'Fernanda', 'Amanda', 'Gabriela', 'Letícia',
        'Mariana', 'Bruna', 'Carolina', 'Isabela', 'Luana', 'Patrícia', 'Renata', 'Aline', 'Vanessa', 'Priscila',
        'Daniela', 'Raquel', 'Sofia', 'Laura', 'Helena', 'Alice', 'Manuela', 'Valentina', 'Lívia', 'Natália',
        'Thaís', 'Débora',
    ];

    public const SOBRENOMES = [
        'Silva', 'Santos', 'Oliveira', 'Souza', 'Pereira', 'Costa', 'Rodrigues', 'Almeida', 'Nascimento', 'Lima',
        'Araújo', 'Fernandes', 'Carvalho', 'Gomes', 'Martins', 'Rocha', 'Ribeiro', 'Alves', 'Monteiro', 'Mendes',
        'Barros', 'Freitas', 'Barbosa', 'Pinto', 'Moura', 'Cavalcante', 'Dias', 'Castro', 'Campos', 'Cardoso',
        'Teixeira', 'Correia', 'Lopes', 'Moreira', 'Nunes', 'Vieira', 'Ramos', 'Machado', 'Pires', 'Batista',
    ];

    public const RUAS = [
        'Rua das Palmeiras', 'Avenida Paulo Pereira Gomes', 'Rua Carlos Lindenberg', 'Avenida Central',
        'Rua dos Ipês', 'Rua Sete de Setembro', 'Avenida Mestre Álvaro', 'Rua das Acácias', 'Rua Pernambuco',
        'Avenida Fernando Ferrari', 'Rua Joaquim Lírio', 'Avenida Dante Michelini', 'Rua Chapot Presvot',
        'Avenida Leitão da Silva', 'Rua Aleixo Neto', 'Rua das Orquídeas',
    ];

    /** Bairros no formato "bairro|cidade" de Serra e Vitória (ES). */
    public const BAIRROS = [
        'Morada de Laranjeiras|Serra', 'Laranjeiras|Serra', 'Carapina|Serra', 'Jacaraípe|Serra', 'Manguinhos|Serra',
        'Valparaíso|Serra', 'Civit II|Serra', 'Novo Horizonte|Serra', 'Jardim da Penha|Vitória',
        'Praia do Canto|Vitória', 'Jardim Camburi|Vitória', 'Mata da Praia|Vitória', 'Bento Ferreira|Vitória',
        'Santa Lúcia|Vitória',
    ];

    /**
     * Gera um CPF matematicamente válido, fictício e único para cada número de ordem.
     *
     * O multiplicador é coprimo com o módulo, então ordens diferentes nunca repetem o CPF.
     */
    public static function cpf(int $ordem): string
    {
        $base = sprintf('%09d', 100000000 + (($ordem + 17) * 48611231) % 900000000);
        $cpf  = $base;

        for ($posicao = 9; $posicao < 11; $posicao++) {
            $soma = 0;
            for ($indice = 0; $indice < $posicao; $indice++) {
                $soma += (int) $cpf[$indice] * (($posicao + 1) - $indice);
            }
            $cpf .= ((10 * $soma) % 11) % 10;
        }

        return $cpf;
    }

    /**
     * Telefone celular de Vitória/Serra só com dígitos: DDD 27, nono dígito 9 e oito dígitos.
     */
    public static function telefone(): string
    {
        return '279' . mt_rand(10000000, 99999999);
    }

    /**
     * Formata o telefone como (27) 9xxxx-xxxx, para textos livres como o contato de emergência.
     */
    public static function telefoneFormatado(string $digitos): string
    {
        return sprintf('(%s) %s-%s', substr($digitos, 0, 2), substr($digitos, 2, 5), substr($digitos, 7));
    }

    /**
     * Tira os acentos de um texto, para montar e-mails.
     */
    public static function semAcentos(string $texto): string
    {
        return strtr(mb_strtolower($texto), [
            'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'é' => 'e', 'ê' => 'e', 'í' => 'i',
            'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ú' => 'u', 'ç' => 'c', ' ' => '.',
        ]);
    }

    /**
     * Data Y-m-d de "N dias atrás".
     */
    public static function diasAtras(int $dias): string
    {
        return date('Y-m-d', strtotime("-{$dias} days"));
    }

    /**
     * Sorteia um endereço de Serra ou Vitória, no formato "Rua X, 123 - Bairro, Cidade/ES".
     */
    public static function endereco(): string
    {
        [$bairro, $cidade] = explode('|', self::BAIRROS[mt_rand(0, count(self::BAIRROS) - 1)]);

        return self::RUAS[mt_rand(0, count(self::RUAS) - 1)] . ', ' . mt_rand(10, 999) . ' - ' . $bairro . ', ' . $cidade . '/ES';
    }
}
