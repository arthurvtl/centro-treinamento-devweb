<?php
/**
 * Ficha da turma com os alunos matriculados e as ações de situação (RF22).
 *
 * @var array<string, mixed>       $turma
 * @var list<array<string, mixed>> $alunos
 */
$ehProprietario = session()->get('usuario_perfil') === 'proprietario';
$situacao       = $turma['situacao'];
$estilos        = ['ATIVA' => 'text-bg-success', 'SUSPENSA' => 'text-bg-warning', 'ENCERRADA' => 'text-bg-secondary'];
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0"><?= esc($turma['nome']) ?>
        <span class="badge <?= esc($estilos[$situacao] ?? 'text-bg-secondary', 'attr') ?> fs-6"><?= esc(ucfirst(strtolower($situacao))) ?></span>
    </h1>
    <div class="d-flex flex-wrap gap-2">
        <?php if ($situacao === 'ATIVA') : ?>
            <a href="<?= url_to('matriculas_nova') ?>?turma_id=<?= esc($turma['id'], 'url') ?>" class="btn btn-primary">Matricular aluno</a>
        <?php endif; ?>
        <?php if ($ehProprietario && $situacao !== 'ENCERRADA') : ?>
            <a href="<?= url_to('turmas_editar', $turma['id']) ?>" class="btn btn-outline-secondary">Editar</a>
            <form action="<?= url_to($situacao === 'ATIVA' ? 'turmas_suspender' : 'turmas_reativar', $turma['id']) ?>" method="post">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-warning"><?= $situacao === 'ATIVA' ? 'Suspender' : 'Reativar' ?></button>
            </form>
        <?php endif; ?>
        <?php if ($ehProprietario) : ?>
            <form action="<?= url_to('turmas_excluir', $turma['id']) ?>" method="post" onsubmit="return confirm('Excluir esta turma?');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-danger">Excluir</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<section class="card card-body mb-4" aria-labelledby="tituloTurma">
    <h2 class="h5" id="tituloTurma">Dados da turma</h2>
    <dl class="row mb-0">
        <dt class="col-sm-3">Modalidade</dt><dd class="col-sm-9"><?= esc($turma['modalidade_nome']) ?></dd>
        <dt class="col-sm-3">Professor</dt><dd class="col-sm-9"><?= esc($turma['professor_nome']) ?></dd>
        <dt class="col-sm-3">Dias e horário</dt>
        <dd class="col-sm-9"><?= esc(str_replace(',', ', ', $turma['dias_semana'])) ?> · <?= esc(substr($turma['hora_inicio'], 0, 5)) ?>–<?= esc(substr($turma['hora_termino'], 0, 5)) ?></dd>
        <dt class="col-sm-3">Vagas</dt><dd class="col-sm-9"><?= esc((string) $turma['ocupadas']) ?> de <?= esc((string) $turma['limite_vagas']) ?> ocupadas</dd>
        <dt class="col-sm-3">Período</dt>
        <dd class="col-sm-9"><?= esc(formatar_data($turma['data_inicio'])) ?> até <?= esc(formatar_data($turma['data_termino'])) ?></dd>
    </dl>
</section>

<section aria-labelledby="tituloAlunos">
    <h2 class="h5" id="tituloAlunos">Alunos matriculados</h2>
    <ul class="list-group">
        <?php if ($alunos === []) : ?>
            <li class="list-group-item text-muted">Nenhum aluno matriculado.</li>
        <?php endif; ?>
        <?php foreach ($alunos as $aluno) : ?>
            <li class="list-group-item"><?= esc($aluno['aluno_codigo']) ?> · <?= esc($aluno['aluno_nome']) ?></li>
        <?php endforeach; ?>
    </ul>
</section>
<a href="<?= url_to('turmas') ?>" class="btn btn-link px-0 mt-3">Voltar para a lista</a>
<?= $this->endSection() ?>
