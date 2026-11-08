<?php
/**
 * Lista de planos de pagamento (RF12).
 *
 * @var list<array<string, mixed>> $planos
 */
$periodicidades = \App\Models\PlanoModel::PERIODICIDADES;
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">Planos</h1>
    <a href="<?= url_to('planos_novo') ?>" class="btn btn-primary">Novo plano</a>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover align-middle bg-white">
        <caption class="visually-hidden">Planos de pagamento</caption>
        <thead>
            <tr>
                <th scope="col">Nome</th>
                <th scope="col">Valor</th>
                <th scope="col">Periodicidade</th>
                <th scope="col">Benefícios</th>
                <th scope="col">Situação</th>
                <th scope="col"><span class="visually-hidden">Ações</span></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($planos === []) : ?>
                <tr><td colspan="6" class="text-center text-muted">Nenhum plano cadastrado.</td></tr>
            <?php endif; ?>
            <?php foreach ($planos as $plano) : ?>
                <?php $ativo = $plano['situacao'] === 'ATIVO'; ?>
                <tr>
                    <td><?= esc($plano['nome']) ?></td>
                    <td><?= esc(formatar_moeda($plano['valor'])) ?></td>
                    <td><?= esc($periodicidades[$plano['periodicidade']] ?? $plano['periodicidade']) ?></td>
                    <td><?= esc($plano['beneficios'] ?: '—') ?></td>
                    <td><span class="badge <?= $ativo ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= $ativo ? 'Ativo' : 'Inativo' ?></span></td>
                    <td class="text-end text-nowrap">
                        <a href="<?= url_to('planos_editar', $plano['id']) ?>" class="btn btn-sm btn-outline-secondary">Editar</a>
                        <form action="<?= url_to($ativo ? 'planos_inativar' : 'planos_reativar', $plano['id']) ?>" method="post" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-warning"><?= $ativo ? 'Inativar' : 'Reativar' ?></button>
                        </form>
                        <form action="<?= url_to('planos_excluir', $plano['id']) ?>" method="post" class="d-inline"
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
<?= $this->endSection() ?>
