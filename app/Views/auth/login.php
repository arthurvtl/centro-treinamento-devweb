<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-4">
        <section class="card" aria-labelledby="tituloLogin">
            <div class="card-body">
                <h1 class="h4 mb-3" id="tituloLogin">Entrar no sistema</h1>
                <form action="<?= esc(site_url('login'), 'html') ?>" method="post" novalidate>
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label for="login_email" class="form-label">Usuário</label>
                        <input type="text" class="form-control" id="login_email" name="login_email" autocomplete="username"
                               autocapitalize="none" spellcheck="false" value="<?= esc(old('login_email'), 'attr') ?>" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label for="senha" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="senha" name="senha" autocomplete="current-password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Entrar</button>
                </form>
            </div>
        </section>
    </div>
</div>
<?= $this->endSection() ?>
