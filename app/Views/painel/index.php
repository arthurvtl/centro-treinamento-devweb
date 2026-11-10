<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<header class="ct-page-header">
    <div>
        <h1 class="h3 mb-0">Painel</h1>
        <p class="text-secondary small mb-0">Resumo do centro e atalhos dos módulos</p>
    </div>
</header>

<div class="row g-3 mb-4">
    <?php foreach ($indicadores as $indicador) : ?>
        <div class="col-6 col-lg">
            <?= view('componentes/indicador', $indicador) ?>
        </div>
    <?php endforeach; ?>
</div>

<section aria-labelledby="tituloAtalhos">
    <h2 class="h5 mb-3" id="tituloAtalhos">Atalhos</h2>
    <div class="row g-3">
        <?php foreach (itens_menu(session()->get('usuario_perfil')) as $item) : ?>
            <div class="col-6 col-md-4 col-lg-3">
                <a class="ct-atalho" href="<?= esc(site_url($item['rota']), 'html') ?>"><?= esc($item['rotulo']) ?></a>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?= $this->endSection() ?>
