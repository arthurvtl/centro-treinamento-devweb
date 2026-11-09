<?php
/**
 * Ficha da turma com os alunos matriculados e as ações de situação (RF22).
 *
 * @var array<string, mixed>       $turma
 * @var list<array<string, mixed>> $alunos
 * @var list<array<string, mixed>> $finalizadas  matrículas finalizadas (turma encerrada)
 */
$ehProprietario = session()->get('usuario_perfil') === 'proprietario';
$situacao       = $turma['situacao'];
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<header class="ct-page-header">
    <h1 class="h3 mb-0"><?= esc($turma['nome']) ?>
        <span class="ct-status <?= esc(classe_status($situacao), 'attr') ?>"><?= esc(ucfirst(strtolower($situacao))) ?></span>
    </h1>
    <div class="d-flex flex-wrap gap-2">
        <?php if ($situacao === 'ATIVA') : ?>
            <a href="<?= esc(url_to('matriculas_nova'), 'html') ?>?turma_id=<?= esc($turma['id'], 'url') ?>" class="btn btn-primary">Matricular aluno</a>
        <?php endif; ?>
        <?php if ($ehProprietario && $situacao !== 'ENCERRADA') : ?>
            <a href="<?= esc(url_to('turmas_editar', $turma['id']), 'html') ?>" class="btn btn-outline-secondary">Editar</a>
            <form action="<?= esc(url_to($situacao === 'ATIVA' ? 'turmas_suspender' : 'turmas_reativar', $turma['id']), 'html') ?>" method="post">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-secondary"><?= $situacao === 'ATIVA' ? 'Suspender' : 'Reativar' ?></button>
            </form>
        <?php endif; ?>
        <?php if ($ehProprietario && $situacao !== 'ENCERRADA') : ?>
            <form action="<?= esc(url_to('turmas_encerrar', $turma['id']), 'html') ?>" method="post"
                  onsubmit="return confirm('Encerrar a turma? As matrículas serão finalizadas e alunos sem outra turma ativa ficarão inativos.');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-dark">Encerrar turma</button>
            </form>
        <?php endif; ?>
        <?php if ($ehProprietario) : ?>
            <form action="<?= esc(url_to('turmas_excluir', $turma['id']), 'html') ?>" method="post" onsubmit="return confirm('Excluir esta turma?');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-danger">Excluir</button>
            </form>
        <?php endif; ?>
    </div>
</header>

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

<?php if ($situacao === 'ENCERRADA') : ?>
    <section class="mb-4" aria-labelledby="tituloFinalizadas">
        <h2 class="h5" id="tituloFinalizadas">Alunos concluintes</h2>
        <ul class="list-group">
            <?php if ($finalizadas === []) : ?>
                <li class="list-group-item text-muted">Nenhuma matrícula foi finalizada nesta turma.</li>
            <?php endif; ?>
            <?php foreach ($finalizadas as $matricula) : ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span><?= esc($matricula['aluno_codigo']) ?> · <?= esc($matricula['aluno_nome']) ?></span>
                    <?php if ((int) $matricula['certificado_emitido'] === 1) : ?>
                        <a href="<?= esc(url_to('matriculas_certificado', $matricula['id']), 'html') ?>" class="btn btn-sm btn-outline-secondary">Certificado</a>
                    <?php else : ?>
                        <span class="ct-status ct-status--inativo">Sem certificado (frequência abaixo de 75%)</span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endif; ?>

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
<a href="<?= esc(url_to('turmas'), 'html') ?>" class="btn btn-link px-0 mt-3">Voltar para a lista</a>
<?= $this->endSection() ?>
