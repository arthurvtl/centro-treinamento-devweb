<?php
/**
 * Consulta de presença (RF10): escolha de turma e data e leitura dos registros da catraca.
 *
 * @var list<array<string, mixed>>   $turmas
 * @var array<string, mixed>|null    $turma         turma escolhida, se houver
 * @var string                       $dataAula
 * @var list<array<string, mixed>>   $alunos        matrículas ativas da turma
 * @var array<int, array<string, mixed>> $lancamentos  registros da catraca nesta data, por matrícula
 */
$turmaAtual = $turma['id'] ?? 0;
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<header class="ct-page-header"><h1 class="h3 mb-0">Frequência</h1></header>

<div class="alert alert-secondary" role="note">
    A presença é registrada automaticamente pela <strong>catraca de acesso</strong> (biometria ou cartão) quando o aluno entra.
    Esta tela é só de consulta: não há lançamento manual.
</div>

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
        <button type="submit" class="btn btn-outline-primary">Consultar</button>
    </div>
</form>

<?php if ($turma !== null) : ?>
    <h2 class="h5">
        <?= esc($turma['nome']) ?> · <?= esc(formatar_data($dataAula)) ?>
        <a href="<?= esc(url_to('frequencias_baixa', $turma['id']), 'html') ?>" class="btn btn-sm btn-outline-secondary ms-2">Alunos abaixo de 75%</a>
    </h2>

    <?php if ($alunos === []) : ?>
        <div class="alert alert-secondary" role="status">Nenhum aluno matriculado nesta turma.</div>
    <?php elseif ($lancamentos === []) : ?>
        <div class="alert alert-secondary" role="status">A catraca não registrou aula desta turma em <?= esc(formatar_data($dataAula)) ?>.</div>
    <?php else : ?>
        <div class="ct-card mb-3">
<div class="table-responsive position-relative" tabindex="0" role="region" aria-label="Presença da turma na data">
            <table class="table table-striped table-hover align-middle">
                <caption class="visually-hidden">Presença registrada pela catraca</caption>
                <thead>
                    <tr>
                        <th scope="col">Aluno</th>
                        <th scope="col">Situação</th>
                        <th scope="col">Observação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alunos as $aluno) : ?>
                        <?php $registro = $lancamentos[(int) $aluno['id']] ?? null; ?>
                        <tr>
                            <td><?= esc($aluno['aluno_codigo']) ?> · <?= esc($aluno['aluno_nome']) ?></td>
                            <td>
                                <?php if ($registro === null) : ?>
                                    <span class="ct-status ct-status--inativo">Sem registro</span>
                                <?php else : ?>
                                    <span class="ct-status <?= $registro['situacao'] === 'PRESENTE' ? 'ct-status--ok' : 'ct-status--alerta' ?>">
                                        <?= $registro['situacao'] === 'PRESENTE' ? 'Presente' : 'Falta' ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td><?= esc($registro['observacao'] ?? '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
</div>
    <?php endif; ?>
<?php endif; ?>
<?= $this->endSection() ?>
