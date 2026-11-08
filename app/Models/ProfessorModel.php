<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Dados e regras de professores e das modalidades em que são habilitados (RF04, RN03).
 */
class ProfessorModel extends Model
{
    protected $table         = 'professores';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['nome', 'cpf', 'telefone', 'email', 'situacao'];

    /** O CPF chega sem máscara: o ProfessorService normaliza antes de validar. */
    protected $validationRules = [
        'id'       => 'permit_empty|is_natural_no_zero', // exigida pelo CI4 para o placeholder {id}
        'nome'     => 'required|min_length[3]|max_length[120]',
        'cpf'      => 'required|cpf_valido|is_unique[professores.cpf,id,{id}]',
        'telefone' => 'required|min_length[10]|max_length[20]',
        'email'    => 'required|valid_email|max_length[150]',
        'situacao' => 'permit_empty|in_list[ATIVO,INATIVO]',
    ];

    protected $validationMessages = [
        'nome' => [
            'required'   => 'Informe o nome do professor.',
            'min_length' => 'O nome deve ter pelo menos 3 caracteres.',
            'max_length' => 'O nome deve ter no máximo 120 caracteres.',
        ],
        'cpf' => [
            'required'   => 'Informe o CPF do professor.',
            'cpf_valido' => 'O CPF informado não é válido.',
            'is_unique'  => 'Já existe um professor cadastrado com este CPF.',
        ],
        'telefone' => [
            'required'   => 'Informe o telefone do professor.',
            'min_length' => 'O telefone deve ter DDD e número.',
            'max_length' => 'O telefone deve ter no máximo 20 caracteres.',
        ],
        'email' => [
            'required'    => 'Informe o e-mail do professor.',
            'valid_email' => 'Informe um e-mail válido.',
            'max_length'  => 'O e-mail deve ter no máximo 150 caracteres.',
        ],
        'situacao' => [
            'in_list' => 'A situação deve ser ATIVO ou INATIVO.',
        ],
    ];

    /**
     * Lista professores com as modalidades que podem ministrar, em ordem alfabética (HU02).
     *
     * @return list<array<string, mixed>> cada item traz `modalidades` com os nomes separados por vírgula
     */
    public function buscarComModalidades(): array
    {
        return $this->select('professores.*, GROUP_CONCAT(modalidades.nome ORDER BY modalidades.nome SEPARATOR ", ") AS modalidades')
            ->join('professor_modalidade', 'professor_modalidade.professor_id = professores.id', 'left')
            ->join('modalidades', 'modalidades.id = professor_modalidade.modalidade_id', 'left')
            ->groupBy('professores.id')
            ->orderBy('professores.nome')
            ->findAll();
    }

    /**
     * Devolve os ids das modalidades em que o professor está habilitado.
     *
     * @return list<int>
     */
    public function buscarIdsModalidades(int $professorId): array
    {
        $linhas = $this->db->table('professor_modalidade')->select('modalidade_id')
            ->where('professor_id', $professorId)->get()->getResultArray();

        return array_map('intval', array_column($linhas, 'modalidade_id'));
    }

    /**
     * Substitui as habilitações do professor pelas modalidades informadas (RF04, RN03).
     *
     * Apaga e regrava: a tabela associativa só tem as duas chaves, então não há o que "atualizar".
     *
     * @param list<int> $modalidadeIds
     */
    public function definirModalidades(int $professorId, array $modalidadeIds): void
    {
        $tabela = $this->db->table('professor_modalidade');
        $tabela->where('professor_id', $professorId)->delete();

        $agora = date('Y-m-d H:i:s');
        foreach (array_unique($modalidadeIds) as $modalidadeId) {
            $tabela->insert([
                'professor_id'  => $professorId,
                'modalidade_id' => $modalidadeId,
                'created_at'    => $agora,
                'updated_at'    => $agora,
            ]);
        }
    }

    /**
     * Informa se o professor está habilitado na modalidade (RN03).
     *
     * Usada pelo cadastro de turmas para impedir professor sem aptidão.
     */
    public function estaHabilitado(int $professorId, int $modalidadeId): bool
    {
        return $this->db->table('professor_modalidade')
            ->where('professor_id', $professorId)
            ->where('modalidade_id', $modalidadeId)
            ->countAllResults() > 0;
    }

    /**
     * Informa se o professor já tem turmas, o que impede excluí-lo.
     */
    public function possuiTurmas(int $professorId): bool
    {
        return $this->db->table('turmas')->where('professor_id', $professorId)->countAllResults() > 0;
    }

    /**
     * Lista os professores ativos em ordem alfabética, para escolher o responsável da turma.
     *
     * @return list<array<string, mixed>>
     */
    public function buscarAtivosOrdenados(): array
    {
        return $this->where('situacao', 'ATIVO')->orderBy('nome')->findAll();
    }

    /**
     * Lista as modalidades em que o professor está habilitado, em ordem alfabética (RN03).
     *
     * @return list<array<string, mixed>>
     */
    public function buscarModalidadesHabilitadas(int $professorId): array
    {
        return $this->db->table('professor_modalidade')
            ->select('modalidades.id, modalidades.nome, modalidades.categoria')
            ->join('modalidades', 'modalidades.id = professor_modalidade.modalidade_id')
            ->where('professor_modalidade.professor_id', $professorId)
            ->orderBy('modalidades.nome')
            ->get()->getResultArray();
    }

    /**
     * Lista as turmas do professor, com a modalidade de cada uma.
     *
     * @return list<array<string, mixed>>
     */
    public function buscarTurmas(int $professorId): array
    {
        return $this->db->table('turmas')
            ->select('turmas.id, turmas.nome, turmas.situacao, turmas.dias_semana, turmas.hora_inicio, turmas.hora_termino, modalidades.nome AS modalidade_nome')
            ->join('modalidades', 'modalidades.id = turmas.modalidade_id')
            ->where('turmas.professor_id', $professorId)
            ->orderBy('turmas.nome')
            ->get()->getResultArray();
    }
}
