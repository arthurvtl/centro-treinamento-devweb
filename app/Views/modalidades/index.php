<?php
/**
 * Lista de modalidades.
 *
 * @var list<array<string, mixed>> $modalidades
 */
$categorias = \App\Models\ModalidadeModel::CATEGORIAS;
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">Modalidades</h1>
    <a href="<?= url_to('modalidades_novo') ?>" class="btn btn-primary">Nova modalidade</a>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover align-middle bg-white">
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
                <tr><td colspan="5" class="text-center text-muted">Nenhuma modalidade cadastrada.</td></tr>
            <?php endif; ?>
            <?php foreach ($modalidades as $modalidade) : ?>
                <tr>
                    <td><?= esc($modalidade['nome']) ?></td>
                    <td><?= esc($categorias[$modalidade['categoria']] ?? $modalidade['categoria']) ?></td>
                    <td><?= esc((string) $modalidade['capacidade_padrao']) ?> alunos</td>
                    <td><?= esc($modalidade['descricao'] ?: '—') ?></td>
                    <td class="text-end text-nowrap">
                        <a href="<?= url_to('modalidades_editar', $modalidade['id']) ?>" class="btn btn-sm btn-outline-secondary">Editar</a>
                        <form action="<?= url_to('modalidades_excluir', $modalidade['id']) ?>" method="post" class="d-inline"
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
<?= $this->endSection() ?>
