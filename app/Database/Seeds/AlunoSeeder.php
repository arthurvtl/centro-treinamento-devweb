<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Alunos de demonstração, incluindo um menor de idade com responsável legal (RF03, RN04),
 * e as matrículas iniciais.
 */
class AlunoSeeder extends Seeder
{
    public function run(): void
    {
        $agora = date('Y-m-d H:i:s');
        $hoje  = date('Y-m-d');

        $alunos = [
            ['AL000001', 'Bruno Carvalho', '1995-04-12', '444555666', 'Nenhuma.'],
            ['AL000002', 'Larissa Souza', '1999-09-30', '555666777', 'Asma leve.'],
            ['AL000003', 'Pedro Henrique Alves', '2012-02-20', '666777888', 'Alergia a amendoim.'], // menor de idade
            ['AL000004', 'Fernanda Rocha', '1988-11-05', '777888999', null],
        ];

        foreach ($alunos as [$codigo, $nome, $nascimento, $baseCpf, $saude]) {
            $this->db->table('alunos')->insert([
                'codigo' => $codigo, 'nome' => $nome, 'data_nascimento' => $nascimento,
                'cpf' => CpfDemonstracao::gerar($baseCpf), 'telefone' => '(27) 98888-0000',
                'email' => strtolower(explode(' ', $nome)[0]) . '@exemplo.com', 'endereco' => 'Rua das Flores, 100 - Serra/ES',
                'data_matricula' => $hoje, 'condicao_saude' => $saude, 'contato_emergencia' => 'Familiar - (27) 97777-0000',
                'situacao' => 'ATIVO', 'created_at' => $agora, 'updated_at' => $agora,
            ]);
        }

        // Responsável do aluno menor (Pedro Henrique, id 3).
        $this->db->table('responsaveis_legais')->insert([
            'aluno_id' => 3, 'nome' => 'Carla Alves', 'cpf' => CpfDemonstracao::gerar('888999000'),
            'telefone' => '(27) 96666-0000', 'email' => 'carla@exemplo.com', 'parentesco' => 'Mãe',
            'created_at' => $agora, 'updated_at' => $agora,
        ]);

        // Matrículas: Bruno e Larissa no jiu-jitsu, Pedro no muay thai kids, Fernanda no vôlei.
        $matriculas = [[1, 1], [2, 1], [3, 2], [4, 3]];
        foreach ($matriculas as [$alunoId, $turmaId]) {
            $this->db->table('matriculas')->insert([
                'aluno_id' => $alunoId, 'turma_id' => $turmaId, 'data_matricula' => $hoje, 'situacao' => 'ATIVA',
                'data_encerramento' => null, 'certificado_emitido' => 0, 'created_at' => $agora, 'updated_at' => $agora,
            ]);
        }
    }
}
