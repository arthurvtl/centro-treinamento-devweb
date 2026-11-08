<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Dados e regras de turmas (RF06, RF22, RN01, RN02, RN03, RN10).
 */
class TurmaModel extends Model
{
    /** Dias da semana na ordem de exibição, com o rótulo mostrado ao usuário. */
    public const DIAS_SEMANA = [
        'SEG' => 'Segunda', 'TER' => 'Terça', 'QUA' => 'Quarta', 'QUI' => 'Quinta',
        'SEX' => 'Sexta', 'SAB' => 'Sábado', 'DOM' => 'Domingo',
    ];

    protected $table         = 'turmas';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'modalidade_id', 'professor_id', 'nome', 'dias_semana', 'hora_inicio', 'hora_termino',
        'limite_vagas', 'situacao', 'data_inicio', 'data_termino',
    ];

    protected $validationRules = [
        'nome'          => 'required|min_length[3]|max_length[80]',
        'modalidade_id' => 'required|is_natural_no_zero|is_not_unique[modalidades.id]',
        'professor_id'  => 'required|is_natural_no_zero|is_not_unique[professores.id]|professor_habilitado[modalidade_id]',
        'dias_semana'   => 'required|max_length[30]',
        'hora_inicio'   => 'required|regex_match[/^\d{2}:\d{2}(:\d{2})?$/]',
        'hora_termino'  => 'required|regex_match[/^\d{2}:\d{2}(:\d{2})?$/]|horario_termino_apos_inicio[hora_inicio]',
        'limite_vagas'  => 'required|is_natural_no_zero|limite_dentro_da_capacidade[modalidade_id]',
        'data_inicio'   => 'required|valid_date[Y-m-d]',
        'data_termino'  => 'permit_empty|valid_date[Y-m-d]|data_termino_valida[data_inicio]',
        'situacao'      => 'permit_empty|in_list[ATIVA,SUSPENSA,ENCERRADA]',
    ];

    protected $validationMessages = [
        'nome' => [
            'required'   => 'Informe o nome da turma.',
            'min_length' => 'O nome deve ter pelo menos 3 caracteres.',
            'max_length' => 'O nome deve ter no máximo 80 caracteres.',
        ],
        'modalidade_id' => [
            'required'           => 'Escolha a modalidade da turma.',
            'is_natural_no_zero' => 'Escolha a modalidade da turma.',
            'is_not_unique'      => 'A modalidade escolhida não existe.',
        ],
        'professor_id' => [
            'required'           => 'Escolha o professor responsável.',
            'is_natural_no_zero' => 'Escolha o professor responsável.',
            'is_not_unique'      => 'O professor escolhido não existe.',
        ],
        'dias_semana' => [
            'required'   => 'Marque pelo menos um dia da semana.',
            'max_length' => 'Os dias da semana excedem o tamanho permitido.',
        ],
        'hora_inicio' => [
            'required'    => 'Informe o horário de início.',
            'regex_match' => 'Informe um horário de início válido.',
        ],
        'hora_termino' => [
            'required'                    => 'Informe o horário de término.',
            'regex_match'                 => 'Informe um horário de término válido.',
            'horario_termino_apos_inicio' => 'O horário de término deve ser depois do início.',
        ],
        'limite_vagas' => [
            'required'           => 'Informe o limite de vagas.',
            'is_natural_no_zero' => 'O limite de vagas deve ser um número maior que zero.',
        ],
        'data_inicio' => [
            'required'   => 'Informe a data de início.',
            'valid_date' => 'Informe uma data de início válida.',
        ],
        'data_termino' => [
            'valid_date'         => 'Informe uma data de término válida.',
            'data_termino_valida' => 'A data de término não pode ser anterior ao início.',
        ],
        'situacao' => [
            'in_list' => 'A situação deve ser ATIVA, SUSPENSA ou ENCERRADA.',
        ],
    ];

    /**
     * Lista turmas com modalidade, professor e vagas ocupadas, para a tela de consulta (RF06, RF18).
     *
     * Só matrículas ATIVAS ocupam vaga: finalizadas, canceladas e transferidas liberam o lugar.
     *
     * @return list<array<string, mixed>>
     */
    public function buscarComOcupacao(?string $situacao = null): array
    {
        $this->select('turmas.*, modalidades.nome AS modalidade_nome, professores.nome AS professor_nome, '
            . '(SELECT COUNT(*) FROM matriculas WHERE matriculas.turma_id = turmas.id AND matriculas.situacao = "ATIVA") AS ocupadas', false)
            ->join('modalidades', 'modalidades.id = turmas.modalidade_id')
            ->join('professores', 'professores.id = turmas.professor_id')
            ->orderBy('turmas.nome');

        if ($situacao !== null) {
            $this->where('turmas.situacao', $situacao);
        }

        return $this->findAll();
    }

    /**
     * Busca uma turma com os nomes de modalidade e professor e as vagas ocupadas.
     *
     * @return array<string, mixed>|null
     */
    public function buscarDetalhe(int $id): ?array
    {
        $lista = $this->select('turmas.*, modalidades.nome AS modalidade_nome, professores.nome AS professor_nome, '
            . '(SELECT COUNT(*) FROM matriculas WHERE matriculas.turma_id = turmas.id AND matriculas.situacao = "ATIVA") AS ocupadas', false)
            ->join('modalidades', 'modalidades.id = turmas.modalidade_id')
            ->join('professores', 'professores.id = turmas.professor_id')
            ->where('turmas.id', $id)
            ->findAll();

        return $lista[0] ?? null;
    }

    /**
     * Conta as matrículas ATIVAS da turma, ou seja, as vagas em uso (RN01, RN02).
     */
    public function contarMatriculasAtivas(int $turmaId): int
    {
        return $this->db->table('matriculas')->where('turma_id', $turmaId)->where('situacao', 'ATIVA')->countAllResults();
    }

    /**
     * Informa se a turma já recebeu alguma matrícula, o que impede excluí-la.
     */
    public function possuiMatriculas(int $turmaId): bool
    {
        return $this->db->table('matriculas')->where('turma_id', $turmaId)->countAllResults() > 0;
    }
}
