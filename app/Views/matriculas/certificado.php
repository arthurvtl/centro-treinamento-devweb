<?php
/**
 * Certificado de conclusão, emitido no encerramento da turma com frequência mínima (RF23, RN09).
 *
 * @var array<string, mixed> $certificado
 */
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<article class="card border-dark border-3 text-center mx-auto my-3 p-4 p-md-5" style="max-width: 760px;" aria-labelledby="tituloCertificado">
    <p class="text-uppercase text-muted mb-1">Sistema CT</p>
    <h1 class="display-6" id="tituloCertificado">Certificado de conclusão</h1>
    <p class="mt-4 mb-1">Certificamos que</p>
    <p class="h2"><?= esc($certificado['aluno_nome']) ?></p>
    <p class="mt-3">
        concluiu a turma <strong><?= esc($certificado['turma_nome']) ?></strong> de
        <strong><?= esc($certificado['modalidade_nome']) ?></strong>, sob orientação do(a) professor(a)
        <?= esc($certificado['professor_nome']) ?>, com frequência igual ou superior a 75%.
    </p>
    <p class="text-muted mb-0">Concluído em <?= esc(formatar_data($certificado['data_encerramento'])) ?> · Código do aluno <?= esc($certificado['aluno_codigo']) ?></p>
</article>
<div class="text-center d-print-none">
    <button type="button" class="btn btn-primary" onclick="window.print()">Imprimir</button>
    <a href="<?= esc(url_to('turmas_ver', $certificado['turma_id']), 'html') ?>" class="btn btn-outline-secondary">Voltar para a turma</a>
</div>
<?= $this->endSection() ?>
