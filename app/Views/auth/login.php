<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-4">
        <section class="card shadow-sm" aria-labelledby="tituloLogin">
            <div class="card-body">
                <h1 class="h4 mb-3" id="tituloLogin">Entrar no sistema</h1>
                <form action="<?= esc(site_url('login'), 'html') ?>" method="post" novalidate>
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label for="login_email" class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="login_email" name="login_email"
                               value="<?= esc(old('login_email')) ?>" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label for="senha" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="senha" name="senha" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Entrar</button>
                </form>
            </div>
        </section>
    </div>
</div>
<?= $this->endSection() ?>
