<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<div class="alert alert-warning" role="alert">
    <h1 class="h4">Acesso negado</h1>
    <p class="mb-2">Seu perfil não tem permissão para acessar esta área.</p>
    <a href="<?= site_url('painel') ?>" class="btn btn-outline-dark btn-sm">Voltar ao painel</a>
</div>
<?= $this->endSection() ?>
