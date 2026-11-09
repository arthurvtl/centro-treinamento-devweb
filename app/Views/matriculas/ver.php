<?php
/**
 * Detalhe de uma matrícula, com as ações possíveis para a situação dela (HU03, RF09, RF23).
 *
 * @var array<string, mixed> $matricula
 */
$ehProprietario = session()->get('usuario_perfil') === 'proprietario';
$situacao       = $matricula['situacao'];
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<header class="ct-page-header">
    <h1 class="h3 mb-0">Matrícula
        <span class="ct-status <?= esc(classe_status($situacao), 'attr') ?>"><?= esc(ucfirst(strtolower($situacao))) ?></span>
    </h1>
    <div class="d-flex flex-wrap gap-2">
        <?php if ($situacao === 'ATIVA') : ?>
            <a href="<?= esc(url_to('matriculas_trocar', $matricula['id']), 'html') ?>" class="btn btn-outline-secondary">Trocar de turma</a>
            <form action="<?= esc(url_to('matriculas_cancelar', $matricula['id']), 'html') ?>" method="post" onsubmit="return confirm('Cancelar esta matrícula?');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-secondary">Cancelar matrícula</button>
            </form>
        <?php endif; ?>
        <?php if ((int) $matricula['certificado_emitido'] === 1) : ?>
            <a href="<?= esc(url_to('matriculas_certificado', $matricula['id']), 'html') ?>" class="btn btn-outline-secondary">Certificado</a>
        <?php endif; ?>
        <?php if ($ehProprietario && $situacao === 'ATIVA') : ?>
            <form action="<?= esc(url_to('matriculas_excluir', $matricula['id']), 'html') ?>" method="post"
                  onsubmit="return confirm('Excluir esta matrícula? Só é possível se ela não tiver frequência.');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-danger">Excluir</button>
            </form>
        <?php endif; ?>
    </div>
</header>

<section class="card card-body mb-4" aria-labelledby="tituloDados">
    <h2 class="h5" id="tituloDados">Dados da matrícula</h2>
    <dl class="row mb-0">
        <dt class="col-sm-3">Aluno</dt>
        <dd class="col-sm-9"><a href="<?= esc(url_to('alunos_ver', $matricula['aluno_id']), 'html') ?>"><?= esc($matricula['aluno_codigo']) ?> · <?= esc($matricula['aluno_nome']) ?></a></dd>
        <dt class="col-sm-3">Turma</dt>
        <dd class="col-sm-9"><a href="<?= esc(url_to('turmas_ver', $matricula['turma_id']), 'html') ?>"><?= esc($matricula['turma_nome']) ?></a> (<?= esc($matricula['modalidade_nome']) ?>)</dd>
        <dt class="col-sm-3">Matriculado em</dt><dd class="col-sm-9"><?= esc(formatar_data($matricula['data_matricula'])) ?></dd>
        <dt class="col-sm-3">Encerrada em</dt><dd class="col-sm-9"><?= esc(formatar_data($matricula['data_encerramento'])) ?></dd>
    </dl>
</section>
<a href="<?= esc(url_to('matriculas'), 'html') ?>" class="btn btn-link px-0">Voltar para a lista</a>
<?= $this->endSection() ?>
