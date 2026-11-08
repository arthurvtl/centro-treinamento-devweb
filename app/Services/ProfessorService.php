<?php

namespace App\Services;

use App\Models\ModalidadeModel;
use App\Models\ProfessorModel;

/**
 * Fluxos de professores que envolvem mais de uma tabela: professor + habilitações (RF04, RN03, HU02).
 */
class ProfessorService
{
    public function __construct(
        private readonly ProfessorModel $professorModel = new ProfessorModel(),
        private readonly ModalidadeModel $modalidadeModel = new ModalidadeModel(),
    ) {
    }

    /**
     * Cadastra o professor e suas modalidades habilitadas, tudo ou nada (RF04).
     *
     * @param array<string, mixed> $dados dados brutos do formulário (inclui `modalidades`, lista de ids)
     * @return array{sucesso: bool, erros: array<string, string>, id: int|null}
     */
    public function cadastrar(array $dados): array
    {
        $professor             = $this->normalizar($dados);
        $professor['situacao'] = 'ATIVO';
        $modalidadeIds         = $this->extrairModalidades($dados);

        $erros = $this->validar($professor, $modalidadeIds);
        if ($erros !== []) {
            return ['sucesso' => false, 'erros' => $erros, 'id' => null];
        }

        $banco = db_connect();
        $banco->transStart();
        $professorId = (int) $this->professorModel->insert($professor, true);
        $this->professorModel->definirModalidades($professorId, $modalidadeIds);
        $banco->transComplete();

        if (! $banco->transStatus()) {
            return ['sucesso' => false, 'erros' => ['geral' => 'Não foi possível salvar o professor. Tente novamente.'], 'id' => null];
        }

        return ['sucesso' => true, 'erros' => [], 'id' => $professorId];
    }

    /**
     * Atualiza os dados e as habilitações do professor (RF04, RN03).
     *
     * @param array<string, mixed> $dados dados brutos do formulário
     * @return array{sucesso: bool, erros: array<string, string>, id: int|null}
     */
    public function atualizar(int $id, array $dados): array
    {
        $professor       = $this->normalizar($dados);
        $professor['id'] = $id; // usado pelo {id} do is_unique, para o CPF do próprio professor não contar como duplicado
        $modalidadeIds   = $this->extrairModalidades($dados);

        $erros = $this->validar($professor, $modalidadeIds);
        if ($erros !== []) {
            return ['sucesso' => false, 'erros' => $erros, 'id' => $id];
        }

        $banco = db_connect();
        $banco->transStart();
        $this->professorModel->update($id, $professor);
        $this->professorModel->definirModalidades($id, $modalidadeIds);
        $banco->transComplete();

        if (! $banco->transStatus()) {
            return ['sucesso' => false, 'erros' => ['geral' => 'Não foi possível atualizar o professor. Tente novamente.'], 'id' => $id];
        }

        return ['sucesso' => true, 'erros' => [], 'id' => $id];
    }

    /**
     * Exclui o professor, desde que não tenha turmas; com turmas o caminho é inativar.
     *
     * @return array{sucesso: bool, mensagem: string}
     */
    public function excluir(int $id): array
    {
        if ($this->professorModel->find($id) === null) {
            return ['sucesso' => false, 'mensagem' => 'Professor não encontrado.'];
        }

        if ($this->professorModel->possuiTurmas($id)) {
            return ['sucesso' => false, 'mensagem' => 'Este professor tem turmas. Inative o cadastro em vez de excluir.'];
        }

        $this->professorModel->delete($id);

        return ['sucesso' => true, 'mensagem' => 'Professor excluído.'];
    }

    /**
     * Valida o professor e confere se há ao menos uma modalidade existente (HU02).
     *
     * @param array<string, mixed> $professor
     * @param list<int>            $modalidadeIds
     * @return array<string, string>
     */
    private function validar(array $professor, array $modalidadeIds): array
    {
        $erros = $this->professorModel->validate($professor) ? [] : $this->professorModel->errors();

        if ($modalidadeIds === []) {
            $erros['modalidades'] = 'Selecione pelo menos uma modalidade em que o professor pode atuar.';
        } elseif ($this->modalidadeModel->whereIn('id', $modalidadeIds)->countAllResults() !== count($modalidadeIds)) {
            $erros['modalidades'] = 'Uma das modalidades selecionadas não existe mais.';
        }

        return $erros;
    }

    /**
     * Seleciona os campos do professor e tira a máscara de CPF e telefone.
     *
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    private function normalizar(array $dados): array
    {
        $professor = array_intersect_key($dados, array_flip(['nome', 'cpf', 'telefone', 'email']));

        $professor['cpf']      = preg_replace('/\D/', '', (string) ($professor['cpf'] ?? ''));
        $professor['telefone'] = preg_replace('/\D/', '', (string) ($professor['telefone'] ?? ''));

        return $professor;
    }

    /**
     * Lê a lista de ids de modalidades marcados no formulário, sem repetições.
     *
     * @param array<string, mixed> $dados
     * @return list<int>
     */
    private function extrairModalidades(array $dados): array
    {
        $marcadas = is_array($dados['modalidades'] ?? null) ? $dados['modalidades'] : [];

        return array_values(array_unique(array_map('intval', $marcadas)));
    }
}
