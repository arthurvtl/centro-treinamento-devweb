<?php
/**
 * Lista de modalidades.
 *
 * @var list<array<string, mixed>> $modalidades
 */
$categorias = rotulos_de('categorias');
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<header class="ct-page-header">
    <h1 class="h3 mb-0">Modalidades</h1>
    <a href="<?= esc(url_to('modalidades_novo'), 'html') ?>" class="btn btn-primary">Nova modalidade</a>
</header>

<div class="ct-card mb-3">
<div class="table-responsive position-relative">
    <table class="table table-striped table-hover align-middle">
        <caption class="visually-hidden">Modalidades cadastradas</caption>
        <thead>
            <tr>
                <th scope="col">Nome</th>
                <th scope="col">Categoria</th>
                <th scope="col">Capacidade padrão</th>
                <th scope="col">Descrição</th>
                <th scope="col"><span class="visually-hidden">Ações</span></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($modalidades === []) : ?>
                <tr><td colspan="5" data-label=""><div class="ct-vazio"><p class="mb-0 fw-semibold">Nenhuma modalidade cadastrada.</p></div></td></tr>
            <?php endif; ?>
            <?php foreach ($modalidades as $modalidade) : ?>
                <tr>
                    <td><?= esc($modalidade['nome']) ?></td>
                    <td><?= esc($categorias[$modalidade['categoria']] ?? $modalidade['categoria']) ?></td>
                    <td><?= esc((string) $modalidade['capacidade_padrao']) ?> alunos</td>
                    <td><?= esc($modalidade['descricao'] ?: '—') ?></td>
                    <td class="text-end text-nowrap">
                        <a href="<?= esc(url_to('modalidades_ver', $modalidade['id']), 'html') ?>" class="btn btn-sm btn-outline-secondary">Ver</a>
                        <a href="<?= esc(url_to('modalidades_editar', $modalidade['id']), 'html') ?>" class="btn btn-sm btn-link">Editar</a>
                        <form action="<?= esc(url_to('modalidades_excluir', $modalidade['id']), 'html') ?>" method="post" class="d-inline"
                              onsubmit="return confirm('Excluir esta modalidade?');">
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
