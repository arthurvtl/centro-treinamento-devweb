<?php
/**
 * Tela de chamada (RF10): escolha de turma e data, e marcação de presença por aluno.
 *
 * @var list<array<string, mixed>>   $turmas
 * @var array<string, mixed>|null    $turma         turma escolhida, se houver
 * @var string                       $dataAula
 * @var list<array<string, mixed>>   $alunos        matrículas ativas da turma
 * @var array<int, array<string, mixed>> $lancamentos  lançamentos já feitos nesta data, por matrícula
 */
$turmaAtual = $turma['id'] ?? 0;
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<h1 class="h3 mb-3">Frequência</h1>

<form action="<?= url_to('frequencias') ?>" method="get" class="row g-2 mb-4">
    <div class="col-12 col-md-6">
        <label for="turma_id" class="form-label">Turma</label>
        <select class="form-select" id="turma_id" name="turma_id" required>
            <option value="">Selecione</option>
            <?php foreach ($turmas as $item) : ?>
                <option value="<?= esc($item['id'], 'attr') ?>" <?= (int) $turmaAtual === (int) $item['id'] ? 'selected' : '' ?>>
                    <?= esc($item['nome']) ?><?= $item['situacao'] === 'SUSPENSA' ? ' (suspensa)' : '' ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-12 col-md-3">
        <label for="data" class="form-label">Data da aula</label>
        <input type="date" class="form-control" id="data" name="data" max="<?= date('Y-m-d') ?>" value="<?= esc($dataAula, 'attr') ?>" required>
    </div>
    <div class="col-12 col-md-3 d-flex align-items-end gap-2">
        <button type="submit" class="btn btn-outline-primary">Abrir chamada</button>
    </div>
</form>

<?php if ($turma !== null) : ?>
    <h2 class="h5">
        <?= esc($turma['nome']) ?> · <?= esc(formatar_data($dataAula)) ?>
        <a href="<?= url_to('frequencias_baixa', $turma['id']) ?>" class="btn btn-sm btn-outline-secondary ms-2">Alunos abaixo de 75%</a>
    </h2>

    <?php if ($turma['situacao'] !== 'ATIVA') : ?>
        <div class="alert alert-warning" role="status">
            Turma <?= esc(strtolower($turma['situacao'])) ?>: não é possível lançar frequência.
        </div>
    <?php elseif ($alunos === []) : ?>
        <div class="alert alert-info" role="status">Nenhum aluno matriculado nesta turma.</div>
    <?php else : ?>
        <form action="<?= url_to('frequencias_registrar') ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="turma_id" value="<?= esc($turma['id'], 'attr') ?>">
            <input type="hidden" name="data_aula" value="<?= esc($dataAula, 'attr') ?>">

            <div class="table-responsive">
                <table class="table table-striped align-middle bg-white">
                    <caption class="visually-hidden">Chamada da turma</caption>
                    <thead>
                        <tr>
                            <th scope="col">Aluno</th>
                            <th scope="col">Situação</th>
                            <th scope="col">Observação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alunos as $aluno) : ?>
                            <?php
                            $matriculaId = (int) $aluno['id'];
                            $anterior    = $lancamentos[$matriculaId] ?? null;
                            // Sem lançamento prévio, a chamada começa com todos presentes para agilizar a recepção.
                            $situacaoAtual = $anterior['situacao'] ?? 'PRESENTE';
                            ?>
                            <tr>
                                <td><?= esc($aluno['aluno_codigo']) ?> · <?= esc($aluno['aluno_nome']) ?></td>
                                <td class="text-nowrap">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="situacao[<?= $matriculaId ?>]"
                                               id="presente_<?= $matriculaId ?>" value="PRESENTE" <?= $situacaoAtual === 'PRESENTE' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="presente_<?= $matriculaId ?>">Presente</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="situacao[<?= $matriculaId ?>]"
                                               id="falta_<?= $matriculaId ?>" value="FALTA" <?= $situacaoAtual === 'FALTA' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="falta_<?= $matriculaId ?>">Falta</label>
                                    </div>
                                </td>
                                <td>
                                    <label class="visually-hidden" for="obs_<?= $matriculaId ?>">Observação de <?= esc($aluno['aluno_nome']) ?></label>
                                    <input type="text" class="form-control form-control-sm" id="obs_<?= $matriculaId ?>"
                                           name="observacao[<?= $matriculaId ?>]" maxlength="255"
                                           value="<?= esc($anterior['observacao'] ?? '', 'attr') ?>">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <button type="submit" class="btn btn-primary">Salvar chamada</button>
        </form>
    <?php endif; ?>
<?php endif; ?>
<?= $this->endSection() ?>
