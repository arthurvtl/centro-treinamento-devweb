<?php
/**
 * Lista de professores com as modalidades habilitadas.
 *
 * @var list<array<string, mixed>> $professores
 */
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">Professores</h1>
    <a href="<?= url_to('professores_novo') ?>" class="btn btn-primary">Novo professor</a>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover align-middle bg-white">
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
                <tr><td colspan="6" class="text-center text-muted">Nenhum professor cadastrado.</td></tr>
            <?php endif; ?>
            <?php foreach ($professores as $professor) : ?>
                <?php $ativo = $professor['situacao'] === 'ATIVO'; ?>
                <tr>
                    <td><?= esc($professor['nome']) ?></td>
                    <td><?= esc(formatar_cpf($professor['cpf'])) ?></td>
                    <td><?= esc(formatar_telefone($professor['telefone'])) ?></td>
                    <td><?= esc($professor['modalidades'] ?: '—') ?></td>
                    <td><span class="badge <?= $ativo ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= $ativo ? 'Ativo' : 'Inativo' ?></span></td>
                    <td class="text-end text-nowrap">
                        <a href="<?= url_to('professores_editar', $professor['id']) ?>" class="btn btn-sm btn-outline-secondary">Editar</a>
                        <form action="<?= url_to($ativo ? 'professores_inativar' : 'professores_reativar', $professor['id']) ?>" method="post" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-warning"><?= $ativo ? 'Inativar' : 'Reativar' ?></button>
                        </form>
                        <form action="<?= url_to('professores_excluir', $professor['id']) ?>" method="post" class="d-inline"
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
<?= $this->endSection() ?>
