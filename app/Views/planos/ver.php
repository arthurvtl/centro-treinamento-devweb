<?php
/**
 * Ficha do plano: dados e alunos com o plano ativo (RF12, RF13).
 *
 * @var array<string, mixed>       $plano
 * @var list<array<string, mixed>> $alunos
 */
$periodicidades = rotulos_de('periodicidades');
$ativo          = $plano['situacao'] === 'ATIVO';
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<header class="ct-page-header">
    <h1 class="h3 mb-0"><?= esc($plano['nome']) ?>
        <span class="ct-status <?= $ativo ? 'ct-status--ok' : 'ct-status--inativo' ?>"><?= $ativo ? 'Ativo' : 'Inativo' ?></span>
    </h1>
    <a href="<?= esc(url_to('planos_editar', $plano['id']), 'html') ?>" class="btn btn-outline-primary">Editar</a>
</header>

<section class="card card-body mb-4" aria-labelledby="tituloDados">
    <h2 class="h5" id="tituloDados">Dados do plano</h2>
    <dl class="row mb-0">
        <dt class="col-sm-3">Valor</dt><dd class="col-sm-9"><?= esc(formatar_moeda($plano['valor'])) ?></dd>
        <dt class="col-sm-3">Periodicidade</dt><dd class="col-sm-9"><?= esc($periodicidades[$plano['periodicidade']] ?? $plano['periodicidade']) ?></dd>
        <dt class="col-sm-3">Benefícios</dt><dd class="col-sm-9"><?= esc($plano['beneficios'] ?: '—') ?></dd>
    </dl>
</section>

<section class="mb-4" aria-labelledby="tituloAlunos">
    <h2 class="h5" id="tituloAlunos">Alunos com este plano (<?= esc((string) count($alunos)) ?>)</h2>
    <div class="ct-card mb-3">
<div class="table-responsive position-relative">
        <table class="table table-striped table-hover align-middle">
            <caption class="visually-hidden">Alunos com o plano ativo</caption>
            <thead>
                <tr>
                    <th scope="col">Aluno</th>
                    <th scope="col">No plano desde</th>
                    <th scope="col"><span class="visually-hidden">Ações</span></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($alunos === []) : ?>
                    <tr><td colspan="3"><div class="ct-vazio"><p class="mb-0 fw-semibold">Nenhum aluno com este plano.</p></div></td></tr>
                <?php endif; ?>
                <?php foreach ($alunos as $aluno) : ?>
                    <tr>
                        <td><?= esc($aluno['codigo']) ?> · <?= esc($aluno['nome']) ?></td>
                        <td><?= esc(formatar_data($aluno['data_inicio'])) ?></td>
                        <td class="text-end"><a href="<?= esc(url_to('mensalidades_aluno', $aluno['id']), 'html') ?>" class="btn btn-sm btn-outline-secondary">Financeiro</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</section>
<a href="<?= esc(url_to('planos'), 'html') ?>" class="btn btn-link px-0">Voltar para a lista</a>
<?= $this->endSection() ?>
