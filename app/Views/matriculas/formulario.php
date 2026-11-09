<?php
/**
 * Formulário de nova matrícula (RF07). Vagas, situação e duplicidade são conferidas no servidor (RF08).
 *
 * @var list<array<string, mixed>> $alunos
 * @var list<array<string, mixed>> $turmas          turmas ativas com vagas ocupadas
 * @var int                        $turmaPreferida  turma pré-selecionada, 0 se nenhuma
 */
$alunoAtual = (int) old('aluno_id', 0);
$turmaAtual = (int) old('turma_id', $turmaPreferida);
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<header class="ct-page-header"><h1 class="h3 mb-0"><?= esc($titulo) ?></h1></header>

<form action="<?= esc(url_to('matriculas_criar'), 'html') ?>" method="post" novalidate>
    <?= csrf_field() ?>
    <div class="card card-body mb-3">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label for="aluno_id" class="form-label">Aluno</label>
                <select class="form-select" id="aluno_id" name="aluno_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($alunos as $aluno) : ?>
                        <option value="<?= esc($aluno['id'], 'attr') ?>" <?= $alunoAtual === (int) $aluno['id'] ? 'selected' : '' ?>>
                            <?= esc($aluno['codigo']) ?> · <?= esc($aluno['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-6">
                <label for="turma_id" class="form-label">Turma</label>
                <select class="form-select" id="turma_id" name="turma_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($turmas as $turma) : ?>
                        <?php $lotada = (int) $turma['ocupadas'] >= (int) $turma['limite_vagas']; ?>
                        <option value="<?= esc($turma['id'], 'attr') ?>" <?= $turmaAtual === (int) $turma['id'] ? 'selected' : '' ?>>
                            <?= esc($turma['nome']) ?> · <?= esc((string) $turma['ocupadas']) ?>/<?= esc((string) $turma['limite_vagas']) ?> vagas<?= $lotada ? ' (lotada)' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
    <div class="ct-form-acoes">
        <button type="submit" class="btn btn-primary">Matricular</button>
        <a href="<?= esc(url_to('matriculas'), 'html') ?>" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
<?= $this->endSection() ?>
