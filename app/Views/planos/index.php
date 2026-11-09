<?php
/**
 * Lista de planos de pagamento (RF12).
 *
 * @var list<array<string, mixed>> $planos
 */
$periodicidades = rotulos_de('periodicidades');
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<header class="ct-page-header">
    <h1 class="h3 mb-0">Planos</h1>
    <a href="<?= esc(url_to('planos_novo'), 'html') ?>" class="btn btn-primary">Novo plano</a>
</header>

<div class="ct-card mb-3">
<div class="table-responsive position-relative">
    <table class="table table-striped table-hover align-middle">
        <caption class="visually-hidden">Planos de pagamento</caption>
        <thead>
            <tr>
                <th scope="col">Nome</th>
                <th scope="col" class="num">Valor</th>
                <th scope="col">Periodicidade</th>
                <th scope="col">Benefícios</th>
                <th scope="col">Situação</th>
                <th scope="col"><span class="visually-hidden">Ações</span></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($planos === []) : ?>
                <tr><td colspan="6"><div class="ct-vazio"><p class="mb-0 fw-semibold">Nenhum plano cadastrado.</p></div></td></tr>
            <?php endif; ?>
            <?php foreach ($planos as $plano) : ?>
                <?php $ativo = $plano['situacao'] === 'ATIVO'; ?>
                <tr>
                    <td><?= esc($plano['nome']) ?></td>
                    <td class="num"><?= esc(formatar_moeda($plano['valor'])) ?></td>
                    <td><?= esc($periodicidades[$plano['periodicidade']] ?? $plano['periodicidade']) ?></td>
                    <td><?= esc($plano['beneficios'] ?: '—') ?></td>
                    <td><span class="ct-status <?= $ativo ? 'ct-status--ok' : 'ct-status--inativo' ?>"><?= $ativo ? 'Ativo' : 'Inativo' ?></span></td>
                    <td class="text-end text-nowrap">
                        <a href="<?= esc(url_to('planos_ver', $plano['id']), 'html') ?>" class="btn btn-sm btn-outline-secondary">Ver</a>
                        <a href="<?= esc(url_to('planos_editar', $plano['id']), 'html') ?>" class="btn btn-sm btn-link">Editar</a>
                        <form action="<?= esc(url_to($ativo ? 'planos_inativar' : 'planos_reativar', $plano['id']), 'html') ?>" method="post" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-secondary"><?= $ativo ? 'Inativar' : 'Reativar' ?></button>
                        </form>
                        <form action="<?= esc(url_to('planos_excluir', $plano['id']), 'html') ?>" method="post" class="d-inline"
                              onsubmit="return confirm('Excluir este plano?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</div>
<?= $this->endSection() ?>
