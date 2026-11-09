<?php
/**
 * Lista de professores com as modalidades habilitadas.
 *
 * @var list<array<string, mixed>> $professores
 */
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<header class="ct-page-header">
    <h1 class="h3 mb-0">Professores</h1>
    <a href="<?= esc(url_to('professores_novo'), 'html') ?>" class="btn btn-primary">Novo professor</a>
</header>

<div class="ct-card mb-3">
<div class="table-responsive position-relative">
    <table class="table table-striped table-hover align-middle">
        <caption class="visually-hidden">Professores cadastrados e modalidades em que atuam</caption>
        <thead>
            <tr>
                <th scope="col">Nome</th>
                <th scope="col">CPF</th>
                <th scope="col">Telefone</th>
                <th scope="col">Modalidades habilitadas</th>
                <th scope="col">Situação</th>
                <th scope="col"><span class="visually-hidden">Ações</span></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($professores === []) : ?>
                <tr><td colspan="6" data-label=""><div class="ct-vazio"><p class="mb-0 fw-semibold">Nenhum professor cadastrado.</p></div></td></tr>
            <?php endif; ?>
            <?php foreach ($professores as $professor) : ?>
                <?php $ativo = $professor['situacao'] === 'ATIVO'; ?>
                <tr>
                    <td><?= esc($professor['nome']) ?></td>
                    <td><?= esc(formatar_cpf($professor['cpf'])) ?></td>
                    <td><?= esc(formatar_telefone($professor['telefone'])) ?></td>
                    <td><?= esc($professor['modalidades'] ?: '—') ?></td>
                    <td><span class="ct-status <?= $ativo ? 'ct-status--ok' : 'ct-status--inativo' ?>"><?= $ativo ? 'Ativo' : 'Inativo' ?></span></td>
                    <td class="text-end text-nowrap">
                        <a href="<?= esc(url_to('professores_ver', $professor['id']), 'html') ?>" class="btn btn-sm btn-outline-secondary">Ver</a>
                        <a href="<?= esc(url_to('professores_editar', $professor['id']), 'html') ?>" class="btn btn-sm btn-link">Editar</a>
                        <form action="<?= esc(url_to($ativo ? 'professores_inativar' : 'professores_reativar', $professor['id']), 'html') ?>" method="post" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-secondary"><?= $ativo ? 'Inativar' : 'Reativar' ?></button>
                        </form>
                        <form action="<?= esc(url_to('professores_excluir', $professor['id']), 'html') ?>" method="post" class="d-inline"
                              onsubmit="return confirm('Excluir este professor?');">
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
