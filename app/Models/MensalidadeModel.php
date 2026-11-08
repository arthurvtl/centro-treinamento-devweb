<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Dados de mensalidades, atraso e adimplência (RF14, RF15, RF24, RF25, RN07, RN11).
 */
class MensalidadeModel extends Model
{
    /** Formas de pagamento aceitas, com rótulo para exibição. */
    public const FORMAS_PAGAMENTO = ['PIX' => 'PIX', 'CARTAO' => 'Cartão', 'DINHEIRO' => 'Dinheiro'];

    /** Situações da mensalidade, com rótulo para exibição. */
    public const SITUACOES = ['EM_ABERTO' => 'Em aberto', 'PAGO' => 'Pago', 'ATRASADO' => 'Atrasado'];

    /** Regras só da quitação: aqui a forma de pagamento passa a ser obrigatória. */
    private const REGRAS_QUITACAO = [
        'data_pagamento'  => 'required|valid_date[Y-m-d]|data_nao_futura',
        'forma_pagamento' => 'required|in_list[PIX,CARTAO,DINHEIRO]',
    ];

    private const MENSAGENS_QUITACAO = [
        'data_pagamento' => [
            'required'        => 'Informe a data do pagamento.',
            'valid_date'      => 'Informe uma data de pagamento válida.',
            'data_nao_futura' => 'A data do pagamento não pode estar no futuro.',
        ],
        'forma_pagamento' => [
            'required' => 'Escolha a forma de pagamento.',
            'in_list'  => 'Escolha uma forma de pagamento válida.',
        ],
    ];

    protected $table         = 'mensalidades';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['aluno_plano_id', 'valor', 'vencimento', 'data_pagamento', 'forma_pagamento', 'situacao'];

    protected $validationRules = [
        'aluno_plano_id'  => 'required|is_natural_no_zero|is_not_unique[aluno_planos.id]',
        'valor'           => 'required|decimal|greater_than[0]|less_than[100000]',
        'vencimento'      => 'required|valid_date[Y-m-d]',
        'data_pagamento'  => 'permit_empty|valid_date[Y-m-d]',
        'forma_pagamento' => 'permit_empty|in_list[PIX,CARTAO,DINHEIRO]',
        'situacao'        => 'permit_empty|in_list[EM_ABERTO,PAGO,ATRASADO]',
    ];

    protected $validationMessages = [
        'aluno_plano_id' => [
            'required'           => 'O aluno não tem plano ativo.',
            'is_natural_no_zero' => 'O aluno não tem plano ativo.',
            'is_not_unique'      => 'O vínculo com o plano não existe.',
        ],
        'valor' => [
            'required'     => 'Informe o valor da mensalidade.',
            'decimal'      => 'Informe o valor em reais, por exemplo 120,00.',
            'greater_than' => 'O valor da mensalidade deve ser maior que zero.',
            'less_than'    => 'O valor da mensalidade está acima do permitido.',
        ],
        'vencimento' => [
            'required'   => 'Informe a data de vencimento.',
            'valid_date' => 'Informe uma data de vencimento válida.',
        ],
        'data_pagamento' => [
            'valid_date' => 'Informe uma data de pagamento válida.',
        ],
        'forma_pagamento' => [
            'in_list' => 'Escolha uma forma de pagamento válida.',
        ],
        'situacao' => [
            'in_list' => 'Situação de mensalidade inválida.',
        ],
    ];

    /**
     * Valida os dados de quitação e devolve os erros em PT-BR (vazio se estiver tudo certo) (RF14).
     *
     * @param array<string, mixed> $dados data_pagamento e forma_pagamento
     * @return array<string, string>
     */
    public function validarQuitacao(array $dados): array
    {
        $validacao = service('validation')->reset()->setRules(self::REGRAS_QUITACAO, self::MENSAGENS_QUITACAO);

        return $validacao->run($dados) ? [] : $validacao->getErrors();
    }

    /**
     * Marca como ATRASADO toda mensalidade em aberto cujo vencimento já passou (RN07, RF25).
     *
     * A situação "atrasada" nunca é digitada: é consequência do vencimento. Chamar isto antes de
     * cada consulta mantém todas as telas coerentes entre si, sem depender de rotina agendada.
     */
    public function atualizarAtrasadas(): void
    {
        $this->db->table($this->table)
            ->where('situacao', 'EM_ABERTO')
            ->where('vencimento <', date('Y-m-d'))
            ->update(['situacao' => 'ATRASADO', 'updated_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Lista mensalidades com aluno e plano, filtrando por situação e por nome ou código do aluno.
     *
     * @return list<array<string, mixed>>
     */
    public function buscarComDetalhes(?string $situacao, ?string $termo, int $porPagina = 10): array
    {
        $this->atualizarAtrasadas();

        $this->select('mensalidades.*, alunos.id AS aluno_id, alunos.nome AS aluno_nome, alunos.codigo AS aluno_codigo, planos.nome AS plano_nome')
            ->join('aluno_planos', 'aluno_planos.id = mensalidades.aluno_plano_id')
            ->join('alunos', 'alunos.id = aluno_planos.aluno_id')
            ->join('planos', 'planos.id = aluno_planos.plano_id')
            ->orderBy('mensalidades.vencimento', 'DESC');

        if (isset(self::SITUACOES[$situacao])) {
            $this->where('mensalidades.situacao', $situacao);
        }

        if ($termo !== null && $termo !== '') {
            $this->groupStart()->like('alunos.nome', $termo)->orLike('alunos.codigo', $termo)->groupEnd();
        }

        return $this->paginate($porPagina);
    }

    /**
     * Lista as mensalidades de um aluno, de todos os planos, da mais recente para a mais antiga (RF24).
     *
     * @return list<array<string, mixed>>
     */
    public function buscarDoAluno(int $alunoId): array
    {
        $this->atualizarAtrasadas();

        return $this->select('mensalidades.*, planos.nome AS plano_nome')
            ->join('aluno_planos', 'aluno_planos.id = mensalidades.aluno_plano_id')
            ->join('planos', 'planos.id = aluno_planos.plano_id')
            ->where('aluno_planos.aluno_id', $alunoId)
            ->orderBy('mensalidades.vencimento', 'DESC')
            ->findAll();
    }

    /**
     * Lista alunos com mensalidades vencidas e não quitadas, do débito mais antigo ao mais novo (RF15).
     *
     * @return list<array<string, mixed>> aluno_id, aluno_nome, aluno_codigo, telefone, quantidade, total, mais_antigo
     */
    public function buscarInadimplentes(): array
    {
        $this->atualizarAtrasadas();

        return $this->db->table('mensalidades')
            ->select('alunos.id AS aluno_id, alunos.nome AS aluno_nome, alunos.codigo AS aluno_codigo, alunos.telefone, '
                . 'COUNT(mensalidades.id) AS quantidade, SUM(mensalidades.valor) AS total, MIN(mensalidades.vencimento) AS mais_antigo', false)
            ->join('aluno_planos', 'aluno_planos.id = mensalidades.aluno_plano_id')
            ->join('alunos', 'alunos.id = aluno_planos.aluno_id')
            ->where('mensalidades.situacao', 'ATRASADO')
            ->groupBy('alunos.id, alunos.nome, alunos.codigo, alunos.telefone')
            ->orderBy('mais_antigo', 'ASC')
            ->get()->getResultArray();
    }

    /**
     * Informa se o aluno está adimplente: sem nenhuma mensalidade vencida e não paga (RN11, RF24).
     *
     * Dívida antiga conta mesmo que o plano tenha sido trocado: trocar de plano não perdoa débito.
     */
    public function alunoEstaAdimplente(int $alunoId): bool
    {
        $this->atualizarAtrasadas();

        return $this->db->table('mensalidades')
            ->join('aluno_planos', 'aluno_planos.id = mensalidades.aluno_plano_id')
            ->where('aluno_planos.aluno_id', $alunoId)
            ->where('mensalidades.situacao', 'ATRASADO')
            ->countAllResults() === 0;
    }

    /**
     * Informa se já existe mensalidade do vínculo com o mesmo vencimento, para não gerar em duplicidade.
     */
    public function existeNoVencimento(int $alunoPlanoId, string $vencimento): bool
    {
        return $this->where('aluno_plano_id', $alunoPlanoId)->where('vencimento', $vencimento)->countAllResults() > 0;
    }

    /**
     * Garante um mês no formato AAAA-MM; qualquer valor inválido vira o mês atual (RF19).
     */
    public function normalizarMes(?string $mes): string
    {
        return ($mes !== null && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $mes)) ? $mes : date('Y-m');
    }

    /**
     * Consolida o financeiro de um mês: previsto, recebido, em aberto, atrasado e recebido por forma (RF19).
     *
     * "Previsto/em aberto/atrasado" olham o mês de vencimento; "recebido" olha o mês em que o
     * dinheiro entrou (data do pagamento), porque é isso que o proprietário confere no caixa.
     *
     * @return array{previsto: float, recebido: float, em_aberto: float, atrasado: float, quantidade: int, por_forma: array<string, float>}
     */
    public function resumirMes(string $mes): array
    {
        $this->atualizarAtrasadas();

        $porVencimento = $this->db->table($this->table)
            ->select('situacao, COUNT(*) AS quantidade, COALESCE(SUM(valor), 0) AS total')
            ->where("DATE_FORMAT(vencimento, '%Y-%m') =", $mes)
            ->groupBy('situacao')->get()->getResultArray();
        $total = array_column($porVencimento, 'total', 'situacao');

        $recebidas = $this->db->table($this->table)
            ->select('forma_pagamento, COALESCE(SUM(valor), 0) AS total')
            ->where('situacao', 'PAGO')
            ->where("DATE_FORMAT(data_pagamento, '%Y-%m') =", $mes)
            ->groupBy('forma_pagamento')->get()->getResultArray();
        $porForma = array_map('floatval', array_column($recebidas, 'total', 'forma_pagamento'));

        return [
            'previsto'   => (float) array_sum($total),
            'recebido'   => array_sum($porForma),
            'em_aberto'  => (float) ($total['EM_ABERTO'] ?? 0),
            'atrasado'   => (float) ($total['ATRASADO'] ?? 0),
            'quantidade' => (int) array_sum(array_column($porVencimento, 'quantidade')),
            'por_forma'  => $porForma,
        ];
    }
}
