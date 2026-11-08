<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Professores e as modalidades em que são habilitados (RF04, RN03).
 *
 * Alguns professores atuam em duas modalidades; as turmas só usam professores habilitados.
 */
class ProfessorSeeder extends Seeder
{
    public function run(): void
    {
        mt_srand(DadosDemonstracao::SEMENTE);
        $agora = date('Y-m-d H:i:s');

        $modalidades = array_column($this->db->table('modalidades')->select('id, nome')->get()->getResultArray(), 'id', 'nome');

        // [nome, e-mail, modalidades em que é habilitado]
        $professores = [
            ['Marcão Gomes', 'marcao', ['Jiu-jitsu', 'Judô']],
            ['Vanclei Moura', 'vanclei', ['Boxe', 'Muay thai']],
            ['Renata Lima', 'renata', ['Vôlei', 'Basquete']],
            ['Carlos Eduardo Souza', 'carlos', ['Futsal']],
            ['Patrícia Nunes', 'patricia', ['Jiu-jitsu']],
            ['Rafael Teixeira', 'rafael', ['Muay thai']],
            ['Juliana Prado', 'juliana', ['Judô']],
            ['Thiago Barbosa', 'thiago', ['Basquete', 'Futsal']],
        ];

        foreach ($professores as $ordem => [$nome, $login, $habilitacoes]) {
            $this->db->table('professores')->insert([
                'nome' => $nome, 'cpf' => DadosDemonstracao::cpf($ordem + 1), 'telefone' => DadosDemonstracao::telefone(),
                'email' => $login . '@ct.local', 'situacao' => 'ATIVO', 'created_at' => $agora, 'updated_at' => $agora,
            ]);
            $professorId = (int) $this->db->insertID();

            foreach ($habilitacoes as $modalidade) {
                $this->db->table('professor_modalidade')->insert([
                    'professor_id' => $professorId, 'modalidade_id' => $modalidades[$modalidade],
                    'created_at' => $agora, 'updated_at' => $agora,
                ]);
            }
        }
    }
}
