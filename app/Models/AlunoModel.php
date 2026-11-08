<?php

namespace App\Models;

use CodeIgniter\Model;
use DateTimeImmutable;

/**
 * Dados e regras do cadastro de alunos (RF01, RF02, RN04, RN06, RNF06).
 */
class AlunoModel extends Model
{
    protected $table         = 'alunos';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;

    // `codigo` está liberado só porque o beforeInsert o grava; o beforeUpdate o descarta (RN06).
    protected $allowedFields = [
        'codigo', 'nome', 'data_nascimento', 'cpf', 'telefone', 'email', 'endereco',
        'data_matricula', 'condicao_saude', 'contato_emergencia', 'situacao',
    ];

    protected $beforeInsert = ['atribuirCodigo'];
    protected $beforeUpdate = ['descartarCodigo'];

    /**
     * O CPF chega sem máscara (o Service normaliza antes de validar). O `{id}` do is_unique
     * faz o próprio aluno não contar como duplicado ao ser editado.
     */
    protected $validationRules = [
        'id'                 => 'permit_empty|is_natural_no_zero', // exigida pelo CI4 para o placeholder {id}
        'nome'               => 'required|min_length[3]|max_length[120]',
        'data_nascimento'    => 'required|valid_date[Y-m-d]|data_nao_futura',
        'cpf'                => 'required|cpf_valido|is_unique[alunos.cpf,id,{id}]',
        'telefone'           => 'required|min_length[10]|max_length[20]',
        'email'              => 'permit_empty|valid_email|max_length[150]',
        'endereco'           => 'required|max_length[255]',
        'data_matricula'     => 'required|valid_date[Y-m-d]',
        'condicao_saude'     => 'permit_empty|max_length[1000]',
        'contato_emergencia' => 'required|max_length[120]',
        'situacao'           => 'permit_empty|in_list[ATIVO,INATIVO]',
    ];

    protected $validationMessages = [
        'nome' => [
            'required'   => 'Informe o nome do aluno.',
            'min_length' => 'O nome deve ter pelo menos 3 caracteres.',
            'max_length' => 'O nome deve ter no máximo 120 caracteres.',
        ],
        'data_nascimento' => [
            'required'        => 'Informe a data de nascimento.',
            'valid_date'      => 'Informe uma data de nascimento válida.',
            'data_nao_futura' => 'A data de nascimento não pode estar no futuro.',
        ],
        'cpf' => [
            'required'   => 'Informe o CPF do aluno.',
            'cpf_valido' => 'O CPF informado não é válido.',
            'is_unique'  => 'Já existe um aluno cadastrado com este CPF.',
        ],
        'telefone' => [
            'required'   => 'Informe o telefone do aluno.',
            'min_length' => 'O telefone deve ter DDD e número.',
            'max_length' => 'O telefone deve ter no máximo 20 caracteres.',
        ],
        'email' => [
            'valid_email' => 'Informe um e-mail válido.',
            'max_length'  => 'O e-mail deve ter no máximo 150 caracteres.',
        ],
        'endereco' => [
            'required'   => 'Informe o endereço do aluno.',
            'max_length' => 'O endereço deve ter no máximo 255 caracteres.',
        ],
        'data_matricula' => [
            'required'   => 'Informe a data de matrícula.',
            'valid_date' => 'Informe uma data de matrícula válida.',
        ],
        'condicao_saude' => [
            'max_length' => 'A condição de saúde deve ter no máximo 1000 caracteres.',
        ],
        'contato_emergencia' => [
            'required'   => 'Informe um contato de emergência.',
            'max_length' => 'O contato de emergência deve ter no máximo 120 caracteres.',
        ],
        'situacao' => [
            'in_list' => 'A situação deve ser ATIVO ou INATIVO.',
        ],
    ];

    /**
     * Gera o código único do aluno, no formato AL000001 (RF02, RN06).
     *
     * Sempre sobrescreve o que vier do formulário, para ninguém escolher o código manualmente.
     *
     * @param array{data: array<string, mixed>} $evento
     * @return array{data: array<string, mixed>}
     */
    protected function atribuirCodigo(array $evento): array
    {
        $ultimo = $this->db->table($this->table)
            ->select('MAX(CAST(SUBSTRING(codigo, 3) AS UNSIGNED)) AS ultimo', false)
            ->get()->getRowArray();

        $evento['data']['codigo'] = 'AL' . str_pad((string) (((int) $ultimo['ultimo']) + 1), 6, '0', STR_PAD_LEFT);

        return $evento;
    }

    /**
     * Remove o código dos dados de atualização: ele é imutável depois de gerado (RN06).
     *
     * @param array{data: array<string, mixed>} $evento
     * @return array{data: array<string, mixed>}
     */
    protected function descartarCodigo(array $evento): array
    {
        unset($evento['data']['codigo']);

        return $evento;
    }

    /**
     * Calcula a idade completa em anos na data de hoje.
     */
    public function calcularIdade(string $dataNascimento): int
    {
        return (new DateTimeImmutable($dataNascimento))->diff(new DateTimeImmutable('today'))->y;
    }

    /**
     * Informa se o aluno é menor de 18 anos e, portanto, exige responsável legal (RN04).
     */
    public function ehMenorDeIdade(string $dataNascimento): bool
    {
        return $this->calcularIdade($dataNascimento) < 18;
    }

    /**
     * Lista alunos para a tela de consulta, com busca por nome, código ou CPF (HU01).
     *
     * Não traz `condicao_saude`: a lista nunca precisa do dado sensível (LGPD, RNF06).
     *
     * @return list<array<string, mixed>>
     */
    public function buscarAlunos(?string $termo, int $porPagina = 10): array
    {
        $this->select('id, codigo, nome, cpf, telefone, data_nascimento, situacao')->orderBy('nome');

        if ($termo !== null && $termo !== '') {
            $somenteDigitos = preg_replace('/\D/', '', $termo);
            $this->groupStart()
                ->like('nome', $termo)
                ->orLike('codigo', $termo);
            if ($somenteDigitos !== '') {
                $this->orLike('cpf', $somenteDigitos);
            }
            $this->groupEnd();
        }

        return $this->paginate($porPagina);
    }

    /**
     * Remove a condição de saúde do aluno quando o perfil não é o proprietário (RNF06, LGPD).
     *
     * A restrição é feita aqui, no servidor, e não apenas escondendo o campo na tela.
     *
     * @param array<string, mixed> $aluno
     * @return array<string, mixed>
     */
    public function aplicarRestricaoLgpd(array $aluno, ?string $perfil): array
    {
        if ($perfil !== 'proprietario') {
            unset($aluno['condicao_saude']);
        }

        return $aluno;
    }

    /**
     * Informa se o aluno tem matrículas ou planos, o que impede a exclusão definitiva.
     */
    public function possuiVinculos(int $alunoId): bool
    {
        $matriculas = $this->db->table('matriculas')->where('aluno_id', $alunoId)->countAllResults();
        $planos     = $this->db->table('aluno_planos')->where('aluno_id', $alunoId)->countAllResults();

        return ($matriculas + $planos) > 0;
    }

    /**
     * Conta os alunos cadastrados, separando ativos e inativos (RF16).
     *
     * @return array{total: int, ativos: int, inativos: int}
     */
    public function contarPorSituacao(): array
    {
        $linhas = $this->db->table($this->table)->select('situacao, COUNT(*) AS quantidade')->groupBy('situacao')->get()->getResultArray();
        $porSituacao = array_column($linhas, 'quantidade', 'situacao');

        $ativos   = (int) ($porSituacao['ATIVO'] ?? 0);
        $inativos = (int) ($porSituacao['INATIVO'] ?? 0);

        return ['total' => $ativos + $inativos, 'ativos' => $ativos, 'inativos' => $inativos];
    }
}
