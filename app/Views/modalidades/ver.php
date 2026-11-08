<?php
/**
 * Ficha da modalidade: dados, professores habilitados (RN03) e turmas (RF05).
 *
 * @var array<string, mixed>       $modalidade
 * @var list<array<string, mixed>> $professores
 * @var list<array<string, mixed>> $turmas
 */
$categorias = rotulos_de('categorias');
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0"><?= esc($modalidade['nome']) ?></h1>
    <a href="<?= esc(url_to('modalidades_editar', $modalidade['id']), 'html') ?>" class="btn btn-outline-primary">Editar</a>
</div>

<section class="card card-body mb-4" aria-labelledby="tituloDados">
    <h2 class="h5" id="tituloDados">Dados da modalidade</h2>
    <dl class="row mb-0">
        <dt class="col-sm-3">Categoria</dt><dd class="col-sm-9"><?= esc($categorias[$modalidade['categoria']] ?? $modalidade['categoria']) ?></dd>
        <dt class="col-sm-3">Capacidade padrão</dt><dd class="col-sm-9"><?= esc((string) $modalidade['capacidade_padrao']) ?> alunos por turma</dd>
        <dt class="col-sm-3">Descrição</dt><dd class="col-sm-9"><?= esc($modalidade['descricao'] ?: '—') ?></dd>
    </dl>
</section>

<section class="mb-4" aria-labelledby="tituloProfessores">
    <h2 class="h5" id="tituloProfessores">Professores habilitados</h2>
    <ul class="list-group">
        <?php if ($professores === []) : ?>
            <li class="list-group-item text-muted">Nenhum professor habilitado.</li>
        <?php endif; ?>
        <?php foreach ($professores as $professor) : ?>
            <li class="list-group-item"><a href="<?= esc(url_to('professores_ver', $professor['id']), 'html') ?>"><?= esc($professor['nome']) ?></a></li>
        <?php endforeach; ?>
    </ul>
</section>

<section class="mb-4" aria-labelledby="tituloTurmas">
    <h2 class="h5" id="tituloTurmas">Turmas</h2>
    <div class="table-responsive position-relative">
        <table class="table table-striped align-middle bg-white">
            <caption class="visually-hidden">Turmas da modalidade</caption>
            <thead>
                <tr>
                    <th scope="col">Turma</th>
                    <th scope="col">Professor</th>
                    <th scope="col">Limite de vagas</th>
                    <th scope="col">Situação</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($turmas === []) : ?>
                    <tr><td colspan="4" class="text-center text-muted">Nenhuma turma.</td></tr>
                <?php endif; ?>
                <?php foreach ($turmas as $turma) : ?>
                    <tr>
                        <td><a href="<?= esc(url_to('turmas_ver', $turma['id']), 'html') ?>"><?= esc($turma['nome']) ?></a></td>
                        <td><?= esc($turma['professor_nome']) ?></td>
                        <td><?= esc((string) $turma['limite_vagas']) ?></td>
                        <td><?= esc(ucfirst(strtolower($turma['situacao']))) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<a href="<?= esc(url_to('modalidades'), 'html') ?>" class="btn btn-link px-0">Voltar para a lista</a>
<?= $this->endSection() ?>
