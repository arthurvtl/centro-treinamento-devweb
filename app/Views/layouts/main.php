<?php
/**
 * Layout base: navbar por perfil (RF21), mensagens de retorno e Bootstrap 5.
 */
// Garante o helper mesmo quando o layout é renderizado por um filtro (ex.: tela de acesso negado).
helper('menu');
$perfil    = session()->get('usuario_perfil');
$ehDono    = $perfil === 'proprietario';
$logado    = session()->get('usuario_id') !== null;
$erros     = session()->getFlashdata('erros') ?? [];
$secaoAtual = service('uri')->getSegment(1);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($titulo ?? 'Sistema CT') ?> · Sistema CT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= esc(base_url('css/app.css'), 'html') ?>" rel="stylesheet">
</head>
<body>
<a class="visually-hidden-focusable" href="#conteudo">Pular para o conteúdo</a>
<header>
    <nav class="navbar navbar-expand-lg bg-white border-bottom" aria-label="Menu principal">
        <div class="container">
            <a class="navbar-brand" href="<?= esc(site_url('painel'), 'html') ?>">Sistema CT</a>
            <?php if ($logado) : ?>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal"
                        aria-controls="menuPrincipal" aria-expanded="false" aria-label="Alternar menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="menuPrincipal">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <?php foreach (itens_menu($perfil) as $item) : ?>
                            <li class="nav-item">
                                <a class="nav-link<?= $secaoAtual === $item['rota'] ? ' active' : '' ?>" href="<?= esc(site_url($item['rota']), 'html') ?>"
                                   <?= $secaoAtual === $item['rota'] ? 'aria-current="page"' : '' ?>><?= esc($item['rotulo']) ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <span class="navbar-text me-3 ct-usuario">
                        <?= esc(session()->get('usuario_nome')) ?>
                        <span class="ct-perfil"><?= esc($ehDono ? 'Proprietário' : 'Recepcionista') ?></span>
                    </span>
                    <form action="<?= esc(site_url('sair'), 'html') ?>" method="post" class="d-inline">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-outline-secondary btn-sm">Sair</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </nav>
</header>

<main class="container py-4" id="conteudo">
    <?php if (session()->getFlashdata('sucesso')) : ?>
        <div class="alert alert-success alert-dismissible fade show" role="status">
            <strong>Pronto:</strong> <?= esc(session()->getFlashdata('sucesso')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('erro')) : ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Atenção:</strong> <?= esc(session()->getFlashdata('erro')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endif; ?>
    <?php if ($erros !== []) : ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <p class="mb-1"><strong>Atenção:</strong> corrija os campos abaixo.</p>
            <ul class="mb-0">
                <?php foreach ($erros as $mensagem) : ?>
                    <li><?= esc($mensagem) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endif; ?>

    <?= $this->renderSection('conteudo') ?>
</main>

<footer class="container pb-4 small text-secondary">Sistema CT · Desenvolvimento Web · IFES</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
