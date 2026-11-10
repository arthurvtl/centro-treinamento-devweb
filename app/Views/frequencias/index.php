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
<header class="ct-page-header"><h1 class="h3 mb-0">Frequência</h1></header>

<form action="<?= esc(url_to('frequencias'), 'html') ?>" method="get" class="row g-2 mb-4">
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
        <a href="<?= esc(url_to('frequencias_baixa', $turma['id']), 'html') ?>" class="btn btn-sm btn-outline-secondary ms-2">Alunos abaixo de 75%</a>
    </h2>

    <?php if ($turma['situacao'] !== 'ATIVA') : ?>
        <div class="alert alert-secondary" role="status">
            Turma <?= esc(strtolower($turma['situacao'])) ?>: não é possível lançar frequência.
        </div>
    <?php elseif ($alunos === []) : ?>
        <div class="alert alert-secondary" role="status">Nenhum aluno matriculado nesta turma.</div>
    <?php else : ?>
        <form action="<?= esc(url_to('frequencias_registrar'), 'html') ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="turma_id" value="<?= esc($turma['id'], 'attr') ?>">
            <input type="hidden" name="data_aula" value="<?= esc($dataAula, 'attr') ?>">

            <div class="ct-card mb-3">
<div class="table-responsive position-relative">
                <table class="table table-striped table-hover align-middle ct-chamada">
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
                            $situacaoAtual = old('situacao.' . $matriculaId, $anterior['situacao'] ?? 'PRESENTE');
                            ?>
                            <tr>
                                <td><?= esc($aluno['aluno_codigo']) ?> · <?= esc($aluno['aluno_nome']) ?></td>
                                <td>
                                    <div class="btn-group" role="group" aria-label="Situação de <?= esc($aluno['aluno_nome']) ?>">
                                        <input type="radio" class="btn-check" name="situacao[<?= esc($matriculaId, 'attr') ?>]"
                                               id="presente_<?= esc($matriculaId, 'attr') ?>" value="PRESENTE" <?= $situacaoAtual === 'PRESENTE' ? 'checked' : '' ?>>
                                        <label class="btn btn-outline-secondary" for="presente_<?= esc($matriculaId, 'attr') ?>">Presente</label>
                                        <input type="radio" class="btn-check" name="situacao[<?= esc($matriculaId, 'attr') ?>]"
                                               id="falta_<?= esc($matriculaId, 'attr') ?>" value="FALTA" <?= $situacaoAtual === 'FALTA' ? 'checked' : '' ?>>
                                        <label class="btn btn-outline-secondary" for="falta_<?= esc($matriculaId, 'attr') ?>">Falta</label>
                                    </div>
                                </td>
                                <td>
                                    <label class="visually-hidden" for="obs_<?= esc($matriculaId, 'attr') ?>">Observação de <?= esc($aluno['aluno_nome']) ?></label>
                                    <input type="text" class="form-control form-control-sm" id="obs_<?= esc($matriculaId, 'attr') ?>"
                                           name="observacao[<?= esc($matriculaId, 'attr') ?>]" maxlength="255"
                                           value="<?= esc(old('observacao.' . $matriculaId, $anterior['observacao'] ?? ''), 'attr') ?>">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
</div>
            <button type="submit" class="btn btn-primary">Salvar chamada</button>
        </form>

        <?php if ($lancamentos !== []) : ?>
            <form action="<?= esc(url_to('frequencias_excluir', $turma['id'], $dataAula), 'html') ?>" method="post" class="mt-3"
                  onsubmit="return confirm('Excluir a chamada desta data? Os lançamentos serão apagados.');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-danger btn-sm">Excluir a chamada de <?= esc(formatar_data($dataAula)) ?></button>
            </form>
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>
<?= $this->endSection() ?>
