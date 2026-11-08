<?php
/**
 * Formulário de troca de turma (RF09): a turma de destino é validada no servidor como numa matrícula nova.
 *
 * @var array<string, mixed>       $matricula
 * @var array<string, mixed>       $aluno
 * @var list<array<string, mixed>> $turmas    turmas ativas, sem a turma atual
 */
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<h1 class="h3 mb-3"><?= esc($titulo) ?></h1>
<p>Aluno: <strong><?= esc($aluno['codigo']) ?> · <?= esc($aluno['nome']) ?></strong></p>

<form action="<?= url_to('matriculas_efetivar_troca', $matricula['id']) ?>" method="post" novalidate>
    <?= csrf_field() ?>
    <div class="card card-body mb-3">
        <label for="turma_id" class="form-label">Nova turma</label>
        <select class="form-select" id="turma_id" name="turma_id" required>
            <option value="">Selecione</option>
            <?php foreach ($turmas as $turma) : ?>
                <?php $lotada = (int) $turma['ocupadas'] >= (int) $turma['limite_vagas']; ?>
                <option value="<?= esc($turma['id'], 'attr') ?>" <?= (int) old('turma_id', 0) === (int) $turma['id'] ? 'selected' : '' ?>>
                    <?= esc($turma['nome']) ?> · <?= esc((string) $turma['ocupadas']) ?>/<?= esc((string) $turma['limite_vagas']) ?> vagas<?= $lotada ? ' (lotada)' : '' ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Trocar de turma</button>
        <a href="<?= url_to('matriculas') ?>" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
<?= $this->endSection() ?>
