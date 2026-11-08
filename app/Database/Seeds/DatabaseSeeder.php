<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Popula o banco com uma demonstração realista do Sistema CT.
 *
 * A ordem respeita as chaves estrangeiras (pais antes dos filhos) e as dependências entre os
 * módulos: matrículas precisam de alunos e turmas; a frequência, das matrículas; as
 * mensalidades, dos planos. O resultado é determinístico (semente fixa), com datas relativas a hoje.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Executa os seeders de cada módulo na ordem de dependência.
     */
    public function run(): void
    {
        $this->call('UsuarioSeeder');       // acesso: proprietário e recepcionista
        $this->call('ModalidadeSeeder');    // 4 lutas e 3 esportes coletivos
        $this->call('ProfessorSeeder');     // professores e habilitações
        $this->call('TurmaSeeder');         // 13 turmas
        $this->call('AlunoSeeder');         // 60 alunos e responsáveis dos menores
        $this->call('MatriculaSeeder');     // matrículas, trocas e cancelamentos
        $this->call('FrequenciaSeeder');    // 8 semanas de chamadas e encerramento da turma iniciante
        $this->call('PlanoSeeder');         // planos e vínculos aluno-plano
        $this->call('MensalidadeSeeder');   // mensalidades pagas, em aberto e atrasadas
    }
}
