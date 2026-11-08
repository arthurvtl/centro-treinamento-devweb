<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Planos de pagamento e vínculos aluno-plano (RF12, RF13, RN08).
 *
 * Cada aluno ATIVO termina com exatamente um plano ATIVO; aluno INATIVO só tem plano ENCERRADO.
 * Seis alunos antigos trocaram de plano: o primeiro ficou ENCERRADO e o novo é o ATIVO.
 *
 * Observação: o modelo de dados aceita as periodicidades MENSAL, TRIMESTRAL e SEMESTRAL (ENUM do DER),
 * por isso não há plano "anual"; o plano "Família Mensal" cobre o desconto para familiares.
 */
class PlanoSeeder extends Seeder
{
    public function run(): void
    {
        mt_srand(DadosDemonstracao::SEMENTE + 3);
        $agora = date('Y-m-d H:i:s');

        $planos = [
            ['Mensal Básico', 120.00, 'MENSAL', 'Uma modalidade, até 3 aulas por semana.'],
            ['Mensal Plus', 160.00, 'MENSAL', 'Duas modalidades à escolha.'],
            ['Trimestral', 330.00, 'TRIMESTRAL', 'Uma modalidade, com desconto de 8%.'],
            ['Semestral', 600.00, 'SEMESTRAL', 'Uma modalidade, com desconto de 17%.'],
            ['Família Mensal', 99.00, 'MENSAL', 'Valor por aluno para dois ou mais familiares; ideal para turmas infantis.'],
        ];
        $ids = [];
        foreach ($planos as [$nome, $valor, $periodicidade, $beneficios]) {
            $this->db->table('planos')->insert([
                'nome' => $nome, 'valor' => $valor, 'periodicidade' => $periodicidade, 'beneficios' => $beneficios,
                'situacao' => 'ATIVO', 'created_at' => $agora, 'updated_at' => $agora,
            ]);
            $ids[$nome] = (int) $this->db->insertID();
        }

        $alunos = $this->db->table('alunos')->select('id, data_matricula, situacao')->orderBy('id')->get()->getResultArray();
        $ativasPorAluno = array_column(
            $this->db->table('matriculas')->select('aluno_id, COUNT(*) AS total')->where('situacao', 'ATIVA')->groupBy('aluno_id')->get()->getResultArray(),
            'total',
            'aluno_id',
        );

        // Quem trocou de plano: seis adultos ativos e antigos (matriculados há mais de 120 dias).
        $antigos = [];
        foreach ($alunos as $i => $aluno) {
            if ($i >= AlunoSeeder::TOTAL_MENORES && $aluno['situacao'] === 'ATIVO' && $aluno['data_matricula'] <= DadosDemonstracao::diasAtras(120)) {
                $antigos[] = $i;
            }
        }
        shuffle($antigos);
        $trocaram = array_slice($antigos, 0, 6);

        foreach ($alunos as $i => $aluno) {
            $inicio = $aluno['data_matricula'];

            if ($aluno['situacao'] !== 'ATIVO') {
                $fim = $this->db->table('matriculas')->selectMax('data_encerramento', 'fim')->where('aluno_id', $aluno['id'])->get()->getRow()->fim
                    ?? DadosDemonstracao::diasAtras(30);
                $this->vincular((int) $aluno['id'], $ids[$this->escolherPlano($i, (int) ($ativasPorAluno[$aluno['id']] ?? 0))], $inicio, $fim, 'ENCERRADO');

                continue;
            }

            if (in_array($i, $trocaram, true)) {
                $dataDaTroca = date('Y-m-d', strtotime($inicio . ' +90 days'));
                $this->vincular((int) $aluno['id'], $ids['Mensal Básico'], $inicio, $dataDaTroca, 'ENCERRADO');
                $inicio = $dataDaTroca;
            }

            $this->vincular((int) $aluno['id'], $ids[$this->escolherPlano($i, (int) ($ativasPorAluno[$aluno['id']] ?? 0))], $inicio, null, 'ATIVO');
        }
    }

    /**
     * Escolhe o plano pelo perfil do aluno: criança no plano família; quem treina em duas turmas
     * tende ao Plus ou aos planos longos; os demais se dividem entre os planos de uma modalidade.
     */
    private function escolherPlano(int $indice, int $turmasAtivas): string
    {
        if ($indice < AlunoSeeder::TOTAL_MENORES) {
            return 'Família Mensal';
        }

        $sorteio = mt_rand(1, 100);

        if ($turmasAtivas >= 2) {
            return $sorteio <= 35 ? 'Mensal Plus' : ($sorteio <= 55 ? 'Mensal Básico' : ($sorteio <= 80 ? 'Trimestral' : 'Semestral'));
        }

        return $sorteio <= 50 ? 'Mensal Básico' : ($sorteio <= 75 ? 'Trimestral' : ($sorteio <= 90 ? 'Semestral' : 'Mensal Plus'));
    }

    /**
     * Grava o vínculo do aluno com o plano.
     */
    private function vincular(int $alunoId, int $planoId, string $inicio, ?string $fim, string $situacao): void
    {
        $this->db->table('aluno_planos')->insert([
            'aluno_id' => $alunoId, 'plano_id' => $planoId, 'data_inicio' => $inicio, 'data_fim' => $fim,
            'situacao' => $situacao, 'created_at' => $inicio . ' 09:00:00', 'updated_at' => $inicio . ' 09:00:00',
        ]);
    }
}
