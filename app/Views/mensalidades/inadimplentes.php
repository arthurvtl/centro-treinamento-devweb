<?php
/**
 * Alunos com mensalidades vencidas e não pagas (RF15, HU05).
 *
 * @var list<array<string, mixed>> $inadimplentes
 */
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<h1 class="h3 mb-1">Inadimplentes</h1>
<p class="text-muted">Alunos com mensalidade vencida e não quitada.</p>

<div class="table-responsive">
    <table class="table table-striped align-middle bg-white">
        <caption class="visually-hidden">Alunos inadimplentes</caption>
        <thead>
            <tr>
                <th scope="col">Aluno</th>
                <th scope="col">Telefone</th>
                <th scope="col">Mensalidades em atraso</th>
                <th scope="col">Total devido</th>
                <th scope="col">Vencida desde</th>
                <th scope="col"><span class="visually-hidden">Ações</span></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($inadimplentes === []) : ?>
                <tr><td colspan="6" class="text-center text-muted">Nenhum aluno inadimplente.</td></tr>
            <?php endif; ?>
            <?php foreach ($inadimplentes as $item) : ?>
                <tr>
                    <td><?= esc($item['aluno_codigo']) ?> · <?= esc($item['aluno_nome']) ?></td>
                    <td><?= esc(formatar_telefone($item['telefone'])) ?></td>
                    <td><?= esc((string) $item['quantidade']) ?></td>
                    <td><?= esc(formatar_moeda($item['total'])) ?></td>
                    <td><?= esc(formatar_data($item['mais_antigo'])) ?></td>
                    <td class="text-end"><a href="<?= url_to('mensalidades_aluno', $item['aluno_id']) ?>" class="btn btn-sm btn-outline-primary">Ver financeiro</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<a href="<?= url_to('mensalidades') ?>" class="btn btn-link px-0">Voltar para as mensalidades</a>
<?= $this->endSection() ?>
