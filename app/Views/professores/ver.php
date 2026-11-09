<?php
/**
 * Ficha do professor: dados, modalidades habilitadas (RN03) e turmas (HU02).
 *
 * @var array<string, mixed>       $professor
 * @var list<array<string, mixed>> $modalidades
 * @var list<array<string, mixed>> $turmas
 */
$ativo = $professor['situacao'] === 'ATIVO';
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<header class="ct-page-header">
    <h1 class="h3 mb-0"><?= esc($professor['nome']) ?>
        <span class="ct-status <?= $ativo ? 'ct-status--ok' : 'ct-status--inativo' ?>"><?= $ativo ? 'Ativo' : 'Inativo' ?></span>
    </h1>
    <a href="<?= esc(url_to('professores_editar', $professor['id']), 'html') ?>" class="btn btn-outline-primary">Editar</a>
</header>

<section class="card card-body mb-4" aria-labelledby="tituloDados">
    <h2 class="h5" id="tituloDados">Dados do professor</h2>
    <dl class="row mb-0">
        <dt class="col-sm-3">CPF</dt><dd class="col-sm-9"><?= esc(formatar_cpf($professor['cpf'])) ?></dd>
        <dt class="col-sm-3">Telefone</dt><dd class="col-sm-9"><?= esc(formatar_telefone($professor['telefone'])) ?></dd>
        <dt class="col-sm-3">E-mail</dt><dd class="col-sm-9"><?= esc($professor['email']) ?></dd>
    </dl>
</section>

<section class="mb-4" aria-labelledby="tituloModalidades">
    <h2 class="h5" id="tituloModalidades">Modalidades em que é habilitado</h2>
    <ul class="list-group">
        <?php if ($modalidades === []) : ?>
            <li class="list-group-item text-muted">Nenhuma modalidade habilitada.</li>
        <?php endif; ?>
        <?php foreach ($modalidades as $modalidade) : ?>
            <li class="list-group-item"><a href="<?= esc(url_to('modalidades_ver', $modalidade['id']), 'html') ?>"><?= esc($modalidade['nome']) ?></a></li>
        <?php endforeach; ?>
    </ul>
</section>

<section class="mb-4" aria-labelledby="tituloTurmas">
    <h2 class="h5" id="tituloTurmas">Turmas</h2>
    <div class="ct-card mb-3">
<div class="table-responsive position-relative">
        <table class="table table-striped table-hover align-middle">
            <caption class="visually-hidden">Turmas do professor</caption>
            <thead>
                <tr>
                    <th scope="col">Turma</th>
                    <th scope="col">Modalidade</th>
                    <th scope="col">Horário</th>
                    <th scope="col">Situação</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($turmas === []) : ?>
                    <tr><td colspan="4"><div class="ct-vazio"><p class="mb-0 fw-semibold">Nenhuma turma.</p></div></td></tr>
                <?php endif; ?>
                <?php foreach ($turmas as $turma) : ?>
                    <tr>
                        <td><a href="<?= esc(url_to('turmas_ver', $turma['id']), 'html') ?>"><?= esc($turma['nome']) ?></a></td>
                        <td><?= esc($turma['modalidade_nome']) ?></td>
                        <td><?= esc(str_replace(',', ', ', $turma['dias_semana'])) ?> · <?= esc(substr($turma['hora_inicio'], 0, 5)) ?>–<?= esc(substr($turma['hora_termino'], 0, 5)) ?></td>
                        <td><?= esc(ucfirst(strtolower($turma['situacao']))) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</section>
<a href="<?= esc(url_to('professores'), 'html') ?>" class="btn btn-link px-0">Voltar para a lista</a>
<?= $this->endSection() ?>
