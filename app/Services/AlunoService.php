<?php

namespace App\Services;

use App\Models\AlunoModel;
use App\Models\ResponsavelLegalModel;

/**
 * Fluxos do cadastro de alunos que envolvem mais de uma tabela (RF01, RF02, RF03, RN04, RN06, RNF06).
 *
 * O Controller só repassa o que veio do formulário; a decisão sobre responsável legal,
 * normalização de CPF e restrição de dado sensível fica aqui.
 */
class AlunoService
{
    private const CAMPOS_RESPONSAVEL = ['nome', 'cpf', 'telefone', 'email', 'parentesco'];

    public function __construct(
        private readonly AlunoModel $alunoModel = new AlunoModel(),
        private readonly ResponsavelLegalModel $responsavelModel = new ResponsavelLegalModel(),
    ) {
    }

    /**
     * Cadastra o aluno e, se for menor de 18 anos, o responsável legal, tudo ou nada (RF01, RF03, RN04).
     *
     * @param array<string, mixed> $dados dados brutos do formulário
     * @return array{sucesso: bool, erros: array<string, string>, id: int|null}
     */
    public function cadastrar(array $dados): array
    {
        $aluno       = $this->normalizarAluno($dados);
        $aluno['situacao'] = 'ATIVO';
        $responsavel = $this->extrairResponsavel($dados);

        $erros = $this->validar($aluno, $responsavel);
        if ($erros !== []) {
            return ['sucesso' => false, 'erros' => $erros, 'id' => null];
        }

        $banco = db_connect();
        $banco->transStart();
        $alunoId = (int) $this->alunoModel->insert($aluno, true);
        if ($this->alunoModel->ehMenorDeIdade($aluno['data_nascimento'])) {
            $this->responsavelModel->insert($responsavel + ['aluno_id' => $alunoId]);
        }
        $banco->transComplete();

        if (! $banco->transStatus()) {
            return ['sucesso' => false, 'erros' => ['geral' => 'Não foi possível salvar o aluno. Tente novamente.'], 'id' => null];
        }

        return ['sucesso' => true, 'erros' => [], 'id' => $alunoId];
    }

    /**
     * Atualiza o aluno e o responsável legal (RF01, RF03, RN04, RN06).
     *
     * Quem não é proprietário não envia nem altera a condição de saúde: o campo é descartado
     * aqui para o valor já gravado permanecer intacto (RNF06). O código nunca é alterado (RN06).
     *
     * @param array<string, mixed> $dados dados brutos do formulário
     * @return array{sucesso: bool, erros: array<string, string>, id: int|null}
     */
    public function atualizar(int $id, array $dados, ?string $perfil): array
    {
        $aluno       = $this->normalizarAluno($dados);
        $responsavel = $this->extrairResponsavel($dados);
        $aluno['id'] = $id; // usado pelo {id} do is_unique, para o CPF do próprio aluno não contar como duplicado

        if ($perfil !== 'proprietario') {
            unset($aluno['condicao_saude']);
        }

        $erros = $this->validar($aluno, $responsavel);
        if ($erros !== []) {
            return ['sucesso' => false, 'erros' => $erros, 'id' => $id];
        }

        $banco = db_connect();
        $banco->transStart();
        $this->alunoModel->update($id, $aluno);
        if ($this->alunoModel->ehMenorDeIdade($aluno['data_nascimento'])) {
            $existente = $this->responsavelModel->buscarPorAluno($id);
            $existente === null
                ? $this->responsavelModel->insert($responsavel + ['aluno_id' => $id])
                : $this->responsavelModel->update($existente['id'], $responsavel);
        }
        $banco->transComplete();

        if (! $banco->transStatus()) {
            return ['sucesso' => false, 'erros' => ['geral' => 'Não foi possível atualizar o aluno. Tente novamente.'], 'id' => $id];
        }

        return ['sucesso' => true, 'erros' => [], 'id' => $id];
    }

    /**
     * Exclui definitivamente o aluno, desde que não tenha matrículas nem planos.
     *
     * Com histórico, o caminho correto é inativar, para não perder frequência e cobranças.
     *
     * @return array{sucesso: bool, mensagem: string}
     */
    public function excluir(int $id): array
    {
        if ($this->alunoModel->find($id) === null) {
            return ['sucesso' => false, 'mensagem' => 'Aluno não encontrado.'];
        }

        if ($this->alunoModel->possuiVinculos($id)) {
            return ['sucesso' => false, 'mensagem' => 'Este aluno tem matrículas ou planos. Inative o cadastro em vez de excluir.'];
        }

        $this->alunoModel->delete($id);

        return ['sucesso' => true, 'mensagem' => 'Aluno excluído.'];
    }

    /**
     * Valida aluno e, quando menor de idade, o responsável legal (RN04).
     *
     * Os erros do responsável recebem o prefixo `responsavel_` para não colidir com os do aluno.
     *
     * @param array<string, mixed> $aluno
     * @param array<string, mixed> $responsavel
     * @return array<string, string>
     */
    private function validar(array $aluno, array $responsavel): array
    {
        $erros = [];

        if (! $this->alunoModel->validate($aluno)) {
            $erros = $this->alunoModel->errors();
        }

        $dataValida = isset($aluno['data_nascimento']) && ! isset($erros['data_nascimento']);
        if ($dataValida && $this->alunoModel->ehMenorDeIdade($aluno['data_nascimento'])
            && ! $this->responsavelModel->validate($responsavel)) {
            foreach ($this->responsavelModel->errors() as $campo => $mensagem) {
                $erros['responsavel_' . $campo] = $mensagem;
            }
        }

        return $erros;
    }

    /**
     * Seleciona os campos do aluno e tira a máscara de CPF e telefone antes de validar e gravar.
     *
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    private function normalizarAluno(array $dados): array
    {
        $aluno = array_intersect_key($dados, array_flip([
            'nome', 'data_nascimento', 'cpf', 'telefone', 'email', 'endereco',
            'data_matricula', 'condicao_saude', 'contato_emergencia',
        ]));

        $aluno['cpf']      = preg_replace('/\D/', '', (string) ($aluno['cpf'] ?? ''));
        $aluno['telefone'] = preg_replace('/\D/', '', (string) ($aluno['telefone'] ?? ''));

        return $aluno;
    }

    /**
     * Monta os dados do responsável a partir dos campos `responsavel_*` do formulário.
     *
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    private function extrairResponsavel(array $dados): array
    {
        $responsavel = [];
        foreach (self::CAMPOS_RESPONSAVEL as $campo) {
            $responsavel[$campo] = $dados['responsavel_' . $campo] ?? '';
        }

        $responsavel['cpf']      = preg_replace('/\D/', '', (string) $responsavel['cpf']);
        $responsavel['telefone'] = preg_replace('/\D/', '', (string) $responsavel['telefone']);

        return $responsavel;
    }
}
