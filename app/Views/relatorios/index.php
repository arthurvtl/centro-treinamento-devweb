<?php
/**
 * Painel de relatórios operacionais (RF16, RF17, RF18).
 *
 * @var array{total: int, ativos: int, inativos: int} $totais
 * @var list<array<string, mixed>>                    $modalidades
 * @var list<array<string, mixed>>                    $turmasComVaga
 */
$categorias     = rotulos_de('categorias');
$ehProprietario = session()->get('usuario_perfil') === 'proprietario';
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<header class="ct-page-header">
    <h1 class="h3 mb-0">Relatórios</h1>
    <?php if ($ehProprietario) : ?>
        <a href="<?= esc(url_to('relatorios_financeiro'), 'html') ?>" class="btn btn-outline-secondary">Relatório financeiro</a>
    <?php endif; ?>
</header>

<section class="mb-4" aria-labelledby="tituloTotais">
    <h2 class="h5" id="tituloTotais">Total de alunos</h2>
    <div class="row g-3">
        <div class="col-12 col-md-4"><div class="card card-body text-center"><span class="fs-2 fw-bold"><?= esc((string) $totais['total']) ?></span><span class="text-muted">cadastrados</span></div></div>
        <div class="col-6 col-md-4"><div class="card card-body text-center"><span class="fs-2 fw-bold text-success"><?= esc((string) $totais['ativos']) ?></span><span class="text-muted">ativos</span></div></div>
        <div class="col-6 col-md-4"><div class="card card-body text-center"><span class="fs-2 fw-bold text-secondary"><?= esc((string) $totais['inativos']) ?></span><span class="text-muted">inativos</span></div></div>
    </div>
</section>

<section class="mb-4" aria-labelledby="tituloModalidades">
    <h2 class="h5" id="tituloModalidades">Alunos por modalidade</h2>
    <p class="text-muted small mb-2">Considera alunos com matrícula ativa; cada aluno conta uma vez por modalidade.</p>
    <div class="ct-card mb-3">
<div class="table-responsive position-relative">
        <table class="table table-striped table-hover align-middle">
            <caption class="visually-hidden">Alunos por modalidade</caption>
            <thead><tr><th scope="col">Modalidade</th><th scope="col">Categoria</th><th scope="col">Alunos</th></tr></thead>
            <tbody>
                <?php if ($modalidades === []) : ?>
                    <tr><td colspan="3"><div class="ct-vazio"><p class="mb-0 fw-semibold">Nenhuma modalidade cadastrada.</p></div></td></tr>
                <?php endif; ?>
                <?php foreach ($modalidades as $modalidade) : ?>
                    <tr>
                        <td><?= esc($modalidade['nome']) ?></td>
                        <td><?= esc($categorias[$modalidade['categoria']] ?? $modalidade['categoria']) ?></td>
                        <td><?= esc((string) $modalidade['alunos']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</section>

<section class="mb-4" aria-labelledby="tituloVagas">
    <h2 class="h5" id="tituloVagas">Turmas com vagas disponíveis</h2>
    <p class="text-muted small mb-2">Só turmas ativas: suspensas e encerradas não aceitam matrícula.</p>
    <div class="ct-card mb-3">
<div class="table-responsive position-relative">
        <table class="table table-striped table-hover align-middle">
            <caption class="visually-hidden">Turmas com vagas</caption>
            <thead>
                <tr><th scope="col">Turma</th><th scope="col">Modalidade</th><th scope="col">Professor</th><th scope="col">Ocupação</th><th scope="col">Vagas livres</th></tr>
            </thead>
            <tbody>
                <?php if ($turmasComVaga === []) : ?>
                    <tr><td colspan="5"><div class="ct-vazio"><p class="mb-0 fw-semibold">Nenhuma turma ativa com vaga.</p></div></td></tr>
                <?php endif; ?>
                <?php foreach ($turmasComVaga as $turma) : ?>
                    <tr>
                        <td><a href="<?= esc(url_to('turmas_ver', $turma['id']), 'html') ?>"><?= esc($turma['nome']) ?></a></td>
                        <td><?= esc($turma['modalidade_nome']) ?></td>
                        <td><?= esc($turma['professor_nome']) ?></td>
                        <td><?= esc((string) $turma['ocupadas']) ?>/<?= esc((string) $turma['limite_vagas']) ?></td>
                        <td><span class="ct-status ct-status--ok"><?= esc((string) $turma['vagas_livres']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</section>
<?= $this->endSection() ?>
