<?php
/**
 * Lista de turmas com vagas. Recepcionista só consulta; proprietário também cadastra.
 *
 * @var list<array<string, mixed>> $turmas
 */
$ehProprietario = session()->get('usuario_perfil') === 'proprietario';
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<header class="ct-page-header">
    <h1 class="h3 mb-0">Turmas</h1>
    <?php if ($ehProprietario) : ?>
        <a href="<?= esc(url_to('turmas_novo'), 'html') ?>" class="btn btn-primary">Nova turma</a>
    <?php endif; ?>
</header>

<div class="ct-card mb-3">
<div class="table-responsive position-relative">
    <table class="table table-striped table-hover align-middle">
        <caption class="visually-hidden">Turmas e vagas ocupadas</caption>
        <thead>
            <tr>
                <th scope="col">Turma</th>
                <th scope="col">Modalidade</th>
                <th scope="col">Professor</th>
                <th scope="col">Horário</th>
                <th scope="col">Vagas</th>
                <th scope="col">Situação</th>
                <th scope="col"><span class="visually-hidden">Ações</span></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($turmas === []) : ?>
                <tr><td colspan="7" data-label=""><div class="ct-vazio"><p class="mb-0 fw-semibold">Nenhuma turma cadastrada.</p></div></td></tr>
            <?php endif; ?>
            <?php foreach ($turmas as $turma) : ?>
                <tr>
                    <td><?= esc($turma['nome']) ?></td>
                    <td><?= esc($turma['modalidade_nome']) ?></td>
                    <td><?= esc($turma['professor_nome']) ?></td>
                    <td><?= esc(str_replace(',', ', ', $turma['dias_semana'])) ?> · <?= esc(substr($turma['hora_inicio'], 0, 5)) ?>–<?= esc(substr($turma['hora_termino'], 0, 5)) ?></td>
                    <td><?= esc((string) $turma['ocupadas']) ?>/<?= esc((string) $turma['limite_vagas']) ?></td>
                    <td><span class="ct-status <?= esc(classe_status($turma['situacao']), 'attr') ?>"><?= esc(ucfirst(strtolower($turma['situacao']))) ?></span></td>
                    <td class="text-end"><a href="<?= esc(url_to('turmas_ver', $turma['id']), 'html') ?>" class="btn btn-sm btn-outline-secondary">Ver</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</div>
<?= $this->endSection() ?>
