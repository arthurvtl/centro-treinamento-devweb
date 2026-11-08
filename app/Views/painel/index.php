<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<h1 class="h3 mb-4">Painel</h1>
<div class="row g-3">
    <?php foreach (itens_menu(session()->get('usuario_perfil')) as $item) : ?>
        <div class="col-6 col-md-4 col-lg-3">
            <a class="card h-100 text-decoration-none shadow-sm" href="<?= site_url($item['rota']) ?>">
                <div class="card-body text-center fw-semibold"><?= esc($item['rotulo']) ?></div>
            </a>
        </div>
    <?php endforeach; ?>
</div>
<?= $this->endSection() ?>
