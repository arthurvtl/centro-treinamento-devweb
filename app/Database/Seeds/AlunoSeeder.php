<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * 60 alunos fictícios com nomes brasileiros variados, CPFs válidos e únicos (RF01, RF02, RF03).
 *
 * - Os 12 primeiros são menores (7 a 16 anos), todos com responsável legal de parentesco variado (RN04).
 * - Os 48 seguintes são adultos de 18 a 60 anos.
 * - A data de matrícula se espalha pelos últimos 12 meses; 10 alunos têm condição de saúde (LGPD).
 * - Dois alunos (índices 17 e 18) ficam INATIVOS por terem cancelado a matrícula. Outros dois
 *   (índices 58 e 59) ficam inativos pelo encerramento da turma iniciante, no FrequenciaSeeder (RN09).
 * - Os códigos AL000001 em diante seguem o formato gerado pelo sistema (RN06).
 */
class AlunoSeeder extends Seeder
{
    /** Quantidade de alunos menores de idade, que ocupam os primeiros índices. */
    public const TOTAL_MENORES = 12;

    public const TOTAL_ALUNOS = 60;

    /** Alunos que cancelaram a matrícula e estão inativos desde o início. */
    private const INATIVOS_POR_CANCELAMENTO = [17, 18];

    public function run(): void
    {
        mt_srand(DadosDemonstracao::SEMENTE);

        $idadesMenores = [7, 8, 8, 9, 10, 11, 12, 12, 13, 14, 15, 16];
        $parentescos   = ['Mãe', 'Pai', 'Mãe', 'Avó', 'Pai', 'Tia', 'Mãe', 'Pai', 'Avô', 'Mãe', 'Tio', 'Pai'];
        $saude         = [
            1  => 'Asma leve, usa bombinha em crises.',
            5  => 'Alergia a amendoim.',
            13 => 'Hipertensão controlada com medicação.',
            19 => 'Lesão antiga no joelho direito; evitar impacto excessivo.',
            24 => 'Diabetes tipo 1; leva lanche para o treino.',
            29 => 'Rinite alérgica.',
            33 => 'Bronquite na infância, sem crises recentes.',
            41 => 'Hérnia de disco lombar tratada; liberado pelo médico.',
            47 => 'Miopia severa; usa lentes de contato nos treinos.',
            52 => 'Cirurgia no ombro esquerdo há 2 anos; liberado.',
        ];

        $usados = [];

        for ($i = 0; $i < self::TOTAL_ALUNOS; $i++) {
            $menor     = $i < self::TOTAL_MENORES;
            $feminino  = $i % 2 === 1;
            $primeiros = $feminino ? DadosDemonstracao::PRIMEIROS_FEMININOS : DadosDemonstracao::PRIMEIROS_MASCULINOS;
            $idade     = $menor ? $idadesMenores[$i] : 18 + ((($i - self::TOTAL_MENORES) * 11) % 43);

            do {
                $sobrenomeA = DadosDemonstracao::SOBRENOMES[mt_rand(0, count(DadosDemonstracao::SOBRENOMES) - 1)];
                $sobrenomeB = DadosDemonstracao::SOBRENOMES[mt_rand(0, count(DadosDemonstracao::SOBRENOMES) - 1)];
                $nome       = $primeiros[mt_rand(0, count($primeiros) - 1)] . ' ' . $sobrenomeA . ' ' . $sobrenomeB;
            } while (isset($usados[$nome]) || $sobrenomeA === $sobrenomeB);
            $usados[$nome] = true;

            // Quem encerra a turma iniciante (índices 48 a 59) e quem cancelou precisa ter se matriculado há tempo.
            $diasDeCasa = match (true) {
                $i >= 48 => mt_rand(130, 350),
                in_array($i, self::INATIVOS_POR_CANCELAMENTO, true) => mt_rand(100, 300),
                // Um em cada nove é aluno novo (matriculado nas últimas semanas), para a demonstração ter recém-chegados.
                $i % 9 === 4 => mt_rand(3, 25),
                default => mt_rand(5, 360),
            };
            $dataMatricula = DadosDemonstracao::diasAtras($diasDeCasa);
            // Subtrair dias extras só adianta o aniversário: a idade em anos continua a escolhida.
            $nascimento = date('Y-m-d', strtotime("-{$idade} years -" . mt_rand(0, 300) . ' days'));

            $telefone = DadosDemonstracao::telefone();
            // Menor: o contato é o próprio responsável (o nome dele está no cadastro de responsável legal).
            $contato = ($menor ? $parentescos[$i] . ' (responsável)' : $this->nomeDeFamiliar($feminino) . ' (familiar)')
                . ' - ' . DadosDemonstracao::telefoneFormatado(DadosDemonstracao::telefone());

            $this->db->table('alunos')->insert([
                'codigo'             => 'AL' . str_pad((string) ($i + 1), 6, '0', STR_PAD_LEFT),
                'nome'               => $nome,
                'data_nascimento'    => $nascimento,
                'cpf'                => DadosDemonstracao::cpf(100 + $i),
                'telefone'           => $telefone,
                'email'              => ($menor && $idade <= 12) ? null : DadosDemonstracao::semAcentos($nome) . '@exemplo.com',
                'endereco'           => DadosDemonstracao::endereco(),
                'data_matricula'     => $dataMatricula,
                'condicao_saude'     => $saude[$i] ?? null,
                'contato_emergencia' => $contato,
                'situacao'           => in_array($i, self::INATIVOS_POR_CANCELAMENTO, true) ? 'INATIVO' : 'ATIVO',
                'created_at'         => $dataMatricula . ' 09:00:00',
                'updated_at'         => $dataMatricula . ' 09:00:00',
            ]);

            if ($menor) {
                $this->criarResponsavel((int) $this->db->insertID(), $i, $parentescos[$i], $sobrenomeB, $dataMatricula);
            }
        }
    }

    /**
     * Cria o responsável legal do aluno menor, com o sobrenome da família (RF03, RN04).
     */
    private function criarResponsavel(int $alunoId, int $indice, string $parentesco, string $sobrenome, string $dataMatricula): void
    {
        $feminino  = in_array($parentesco, ['Mãe', 'Avó', 'Tia'], true);
        $primeiros = $feminino ? DadosDemonstracao::PRIMEIROS_FEMININOS : DadosDemonstracao::PRIMEIROS_MASCULINOS;
        $nome      = $primeiros[mt_rand(0, count($primeiros) - 1)] . ' ' . DadosDemonstracao::SOBRENOMES[mt_rand(0, count(DadosDemonstracao::SOBRENOMES) - 1)] . ' ' . $sobrenome;

        $this->db->table('responsaveis_legais')->insert([
            'aluno_id'   => $alunoId,
            'nome'       => $nome,
            'cpf'        => DadosDemonstracao::cpf(300 + $indice),
            'telefone'   => DadosDemonstracao::telefone(),
            'email'      => DadosDemonstracao::semAcentos($nome) . '@exemplo.com',
            'parentesco' => $parentesco,
            'created_at' => $dataMatricula . ' 09:00:00',
            'updated_at' => $dataMatricula . ' 09:00:00',
        ]);
    }

    /**
     * Sorteia nome e sobrenome de um familiar de adulto, para o contato de emergência.
     */
    private function nomeDeFamiliar(bool $alunoFeminino): string
    {
        // Contato do sexo oposto, como cônjuge, é o mais comum entre adultos.
        $primeiros = $alunoFeminino ? DadosDemonstracao::PRIMEIROS_MASCULINOS : DadosDemonstracao::PRIMEIROS_FEMININOS;

        return $primeiros[mt_rand(0, count($primeiros) - 1)] . ' ' . DadosDemonstracao::SOBRENOMES[mt_rand(0, count(DadosDemonstracao::SOBRENOMES) - 1)];
    }
}
