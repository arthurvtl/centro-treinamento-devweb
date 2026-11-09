<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<header class="ct-page-header"><h1 class="h3 mb-0">Painel</h1></header>
<div class="row g-3">
    <?php foreach (itens_menu(session()->get('usuario_perfil')) as $item) : ?>
        <div class="col-6 col-md-4 col-lg-3">
            <a class="card h-100 text-decoration-none shadow-sm" href="<?= esc(site_url($item['rota']), 'html') ?>">
                <div class="card-body text-center fw-semibold"><?= esc($item['rotulo']) ?></div>
            </a>
        </div>
    <?php endforeach; ?>
</div>
<?= $this->endSection() ?>
