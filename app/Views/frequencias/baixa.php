<?php
/**
 * Presença por aluno da turma, com destaque para quem está abaixo do mínimo (RF11, RN09).
 *
 * @var array<string, mixed>       $turma
 * @var list<array<string, mixed>> $resumos
 * @var int                        $minimo
 */
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<header class="ct-page-header">
    <div>
        <h1 class="h3 mb-0"><?= esc($titulo) ?></h1>
        <p class="text-secondary small mb-0"><?= esc($turma['nome']) ?> · considera as aulas já lançadas para cada aluno.</p>
    </div>
</header>

<div class="ct-card mb-3">
<div class="table-responsive position-relative">
    <table class="table table-striped table-hover align-middle">
        <caption class="visually-hidden">Presença dos alunos da turma</caption>
        <thead>
            <tr>
                <th scope="col">Aluno</th>
                <th scope="col">Presenças</th>
                <th scope="col">Aulas lançadas</th>
                <th scope="col">Frequência</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($resumos === []) : ?>
                <tr><td colspan="4" data-label=""><div class="ct-vazio"><p class="mb-0 fw-semibold">Nenhum aluno matriculado.</p></div></td></tr>
            <?php endif; ?>
            <?php foreach ($resumos as $resumo) : ?>
                <?php $abaixo = $resumo['percentual'] !== null && $resumo['percentual'] < $minimo; ?>
                <tr class="<?= $abaixo ? 'ct-linha-alerta' : '' ?>">
                    <td><?= esc($resumo['aluno_codigo']) ?> · <?= esc($resumo['aluno_nome']) ?></td>
                    <td><?= esc((string) $resumo['presencas']) ?></td>
                    <td><?= esc((string) $resumo['aulas']) ?></td>
                    <td>
                        <?php if ($resumo['percentual'] === null) : ?>
                            <span class="text-muted">Sem lançamentos</span>
                        <?php else : ?>
                            <?= esc(number_format((float) $resumo['percentual'], 1, ',', '.')) ?>%
                            <?php if ($abaixo) : ?><span class="ct-status ct-status--alerta">Abaixo de <?= esc((string) $minimo) ?>%</span><?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</div>
<a href="<?= esc(url_to('frequencias'), 'html') ?>?turma_id=<?= esc($turma['id'], 'url') ?>" class="btn btn-link px-0">Voltar para a chamada</a>
<?= $this->endSection() ?>
