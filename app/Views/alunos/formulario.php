<?php
/**
 * Formulário único de cadastro e edição de aluno (RF01, RF03).
 *
 * @var array<string, mixed> $aluno       valores atuais (vazio no cadastro)
 * @var array<string, mixed> $responsavel responsável legal atual, se houver
 * @var string               $acao        URL de destino do POST
 */
$edicao        = isset($aluno['id']);
$ehProprietario = session()->get('usuario_perfil') === 'proprietario';
// Na edição, só o proprietário vê e altera a condição de saúde (LGPD); no cadastro, qualquer perfil pode informar.
$mostraSaude   = ! $edicao || $ehProprietario;
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<header class="ct-page-header"><h1 class="h3 mb-0"><?= esc($titulo) ?></h1></header>

<form action="<?= esc($acao, 'html') ?>" method="post" novalidate>
    <?= csrf_field() ?>

    <fieldset class="card card-body mb-4">
        <legend class="h5">Dados do aluno</legend>
        <?php if ($edicao) : ?>
            <p class="mb-3">Código: <strong><?= esc($aluno['codigo']) ?></strong>
                <span class="text-muted">(gerado automaticamente, não pode ser alterado)</span></p>
        <?php endif; ?>
        <div class="row g-3">
            <div class="col-12 col-md-8">
                <label for="nome" class="form-label">Nome completo</label>
                <input type="text" class="form-control" id="nome" name="nome" maxlength="120" required
                       value="<?= esc(old('nome', $aluno['nome'] ?? '')) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label for="data_nascimento" class="form-label">Data de nascimento</label>
                <input type="date" class="form-control" id="data_nascimento" name="data_nascimento" required
                       max="<?= date('Y-m-d') ?>" value="<?= esc(old('data_nascimento', $aluno['data_nascimento'] ?? '')) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label for="cpf" class="form-label">CPF</label>
                <input type="text" class="form-control" id="cpf" name="cpf" inputmode="numeric" maxlength="14" required
                       value="<?= esc(old('cpf', isset($aluno['cpf']) ? formatar_cpf($aluno['cpf']) : '')) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label for="telefone" class="form-label">Telefone</label>
                <input type="tel" class="form-control" id="telefone" name="telefone" maxlength="20" required
                       value="<?= esc(old('telefone', isset($aluno['telefone']) ? formatar_telefone($aluno['telefone']) : '')) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label for="email" class="form-label">E-mail <span class="opcional">(opcional)</span></label>
                <input type="email" class="form-control" id="email" name="email" maxlength="150"
                       value="<?= esc(old('email', $aluno['email'] ?? '')) ?>">
            </div>
            <div class="col-12 col-md-8">
                <label for="endereco" class="form-label">Endereço</label>
                <input type="text" class="form-control" id="endereco" name="endereco" maxlength="255" required
                       value="<?= esc(old('endereco', $aluno['endereco'] ?? '')) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label for="data_matricula" class="form-label">Data de matrícula</label>
                <input type="date" class="form-control" id="data_matricula" name="data_matricula" required
                       value="<?= esc(old('data_matricula', $aluno['data_matricula'] ?? '')) ?>">
            </div>
            <div class="col-12">
                <label for="contato_emergencia" class="form-label">Contato de emergência</label>
                <input type="text" class="form-control" id="contato_emergencia" name="contato_emergencia" maxlength="120" required
                       value="<?= esc(old('contato_emergencia', $aluno['contato_emergencia'] ?? '')) ?>">
            </div>
            <?php if ($mostraSaude) : ?>
                <div class="col-12">
                    <label for="condicao_saude" class="form-label">Condição de saúde <span class="opcional">(opcional)</span></label>
                    <textarea class="form-control" id="condicao_saude" name="condicao_saude" rows="2" maxlength="1000"
                              aria-describedby="ajudaSaude"><?= esc(old('condicao_saude', $aluno['condicao_saude'] ?? '')) ?></textarea>
                    <div id="ajudaSaude" class="form-text">Dado sensível (LGPD): somente o proprietário consegue consultar depois.</div>
                </div>
            <?php endif; ?>
        </div>
    </fieldset>

    <fieldset class="card card-body mb-4" id="blocoResponsavel">
        <legend class="h5">Responsável legal <span class="opcional">(obrigatório para menores de 18 anos)</span></legend>
        <div class="row g-3">
            <div class="col-12 col-md-8">
                <label for="responsavel_nome" class="form-label">Nome do responsável</label>
                <input type="text" class="form-control" id="responsavel_nome" name="responsavel_nome" maxlength="120"
                       value="<?= esc(old('responsavel_nome', $responsavel['nome'] ?? '')) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label for="responsavel_parentesco" class="form-label">Parentesco</label>
                <input type="text" class="form-control" id="responsavel_parentesco" name="responsavel_parentesco" maxlength="30"
                       value="<?= esc(old('responsavel_parentesco', $responsavel['parentesco'] ?? '')) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label for="responsavel_cpf" class="form-label">CPF do responsável</label>
                <input type="text" class="form-control" id="responsavel_cpf" name="responsavel_cpf" inputmode="numeric" maxlength="14"
                       value="<?= esc(old('responsavel_cpf', isset($responsavel['cpf']) ? formatar_cpf($responsavel['cpf']) : '')) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label for="responsavel_telefone" class="form-label">Telefone do responsável</label>
                <input type="tel" class="form-control" id="responsavel_telefone" name="responsavel_telefone" maxlength="20"
                       value="<?= esc(old('responsavel_telefone', isset($responsavel['telefone']) ? formatar_telefone($responsavel['telefone']) : '')) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label for="responsavel_email" class="form-label">E-mail do responsável <span class="opcional">(opcional)</span></label>
                <input type="email" class="form-control" id="responsavel_email" name="responsavel_email" maxlength="150"
                       value="<?= esc(old('responsavel_email', $responsavel['email'] ?? '')) ?>">
            </div>
        </div>
    </fieldset>

    <div class="ct-form-acoes">
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="<?= esc(url_to('alunos'), 'html') ?>" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>

<script>
    // Conveniência visual: esconde o bloco do responsável para maiores de 18 anos.
    // A obrigatoriedade é sempre conferida no servidor (RN04); sem JavaScript o bloco fica visível.
    (function () {
        var campoNascimento = document.getElementById('data_nascimento');
        var blocoResponsavel = document.getElementById('blocoResponsavel');

        function atualizarBloco() {
            if (!campoNascimento.value) { blocoResponsavel.classList.remove('d-none'); return; }
            var nascimento = new Date(campoNascimento.value + 'T00:00:00');
            var hoje = new Date();
            var idade = hoje.getFullYear() - nascimento.getFullYear();
            var jaFezAniversario = hoje.getMonth() > nascimento.getMonth()
                || (hoje.getMonth() === nascimento.getMonth() && hoje.getDate() >= nascimento.getDate());
            if (!jaFezAniversario) { idade--; }
            blocoResponsavel.classList.toggle('d-none', idade >= 18);
        }

        campoNascimento.addEventListener('change', atualizarBloco);
        atualizarBloco();
    })();
</script>
<?= $this->endSection() ?>
