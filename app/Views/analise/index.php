<?php
/**
 * Análise do negócio: visão gerencial do proprietário (HU08, RF29, RN05).
 *
 * Cada gráfico tem uma tabela com os mesmos números logo abaixo, para quem usa leitor de tela ou
 * está sem acesso à internet (a biblioteca de gráficos vem de CDN).
 *
 * @var array<string, mixed> $indicadores
 * @var list<array<string, mixed>> $serie
 * @var list<array<string, mixed>> $turmas
 * @var list<array<string, mixed>> $alunos_modalidade
 * @var list<array<string, mixed>> $frequencia
 * @var list<array<string, mixed>> $atraso_faixas
 * @var list<array<string, mixed>> $receita_planos
 * @var list<array<string, mixed>> $loja_categorias
 * @var list<array<string, mixed>> $loja_top
 * @var int $janela_dias
 * @var int $janela_evasao_dias
 * @var int $meses_na_serie
 */
$categoriasProduto = rotulos_de('categorias_produto');
$ind = $indicadores;

// Variação da receita contra o mês anterior, escrita com sinal.
$variacao = $ind['variacao_receita'] === null
    ? 'sem mês anterior para comparar'
    : (($ind['variacao_receita'] >= 0 ? '+' : '') . number_format($ind['variacao_receita'], 1, ',', '.') . '% vs mês anterior');

$percentual = static fn (float $valor): string => number_format($valor, 1, ',', '.') . '%';

// Dados dos gráficos, em JSON seguro para ficar dentro de <script>.
$json = static fn (mixed $dados): string => json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$graficos = [
    'receita'    => [
        'rotulos'      => array_column($serie, 'rotulo'),
        'mensalidades' => array_map(static fn ($v) => round($v, 2), array_column($serie, 'mensalidades')),
        'loja'         => array_map(static fn ($v) => round($v, 2), array_column($serie, 'loja')),
    ],
    'matriculas' => ['rotulos' => array_column($serie, 'rotulo'), 'novas' => array_column($serie, 'novas')],
    'ocupacao'   => ['rotulos' => array_column($turmas, 'nome'), 'valores' => array_column($turmas, 'percentual')],
    'modalidade' => ['rotulos' => array_column($alunos_modalidade, 'modalidade'), 'valores' => array_column($alunos_modalidade, 'alunos')],
    'frequencia' => ['rotulos' => array_column($frequencia, 'modalidade'), 'valores' => array_column($frequencia, 'percentual')],
];
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<header class="ct-page-header">
    <div>
        <h1 class="h3 mb-0">Análise do negócio</h1>
        <p class="text-secondary small mb-0">Receita, inadimplência, ocupação, retenção, frequência e loja num só lugar</p>
    </div>
    <a href="<?= esc(url_to('relatorios_financeiro'), 'html') ?>" class="btn btn-outline-secondary">Relatório financeiro</a>
</header>

<h2 class="h5 mb-3">Financeiro</h2>
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><?= view('componentes/indicador', ['id' => 'kpiReceita', 'rotulo' => 'Receita do mês', 'valor' => formatar_moeda($ind['receita_mes']), 'legenda' => $variacao]) ?></div>
    <div class="col-6 col-lg-3"><?= view('componentes/indicador', ['id' => 'kpiTicketAluno', 'rotulo' => 'Mensalidade por aluno', 'valor' => formatar_moeda($ind['ticket_aluno']), 'legenda' => 'recebido no mês ÷ alunos ativos']) ?></div>
    <div class="col-6 col-lg-3"><?= view('componentes/indicador', ['id' => 'kpiInadimplencia', 'rotulo' => 'Inadimplência', 'valor' => $percentual($ind['inadimplencia']), 'legenda' => $ind['alunos_atrasados'] . ' aluno(s) com atraso', 'alerta' => $ind['inadimplencia'] >= 10]) ?></div>
    <div class="col-6 col-lg-3"><?= view('componentes/indicador', ['id' => 'kpiMargemLoja', 'rotulo' => 'Margem da loja', 'valor' => $percentual($ind['margem_loja']), 'legenda' => 'lucro bruto sobre vendas']) ?></div>
</div>

<h2 class="h5 mb-3">Alunos e turmas</h2>
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><?= view('componentes/indicador', ['id' => 'kpiAlunos', 'rotulo' => 'Alunos ativos', 'valor' => (string) $ind['alunos_ativos'], 'legenda' => 'cadastros em uso']) ?></div>
    <div class="col-6 col-lg-3"><?= view('componentes/indicador', ['id' => 'kpiOcupacao', 'rotulo' => 'Ocupação das turmas', 'valor' => $percentual($ind['ocupacao_media']), 'legenda' => 'vagas preenchidas']) ?></div>
    <div class="col-6 col-lg-3"><?= view('componentes/indicador', ['id' => 'kpiEvasao', 'rotulo' => 'Evasão (' . $janela_evasao_dias . ' dias)', 'valor' => $percentual($ind['evasao']), 'legenda' => $ind['cancelamentos'] . ' matrícula(s) cancelada(s)']) ?></div>
    <div class="col-6 col-lg-3"><?= view('componentes/indicador', ['id' => 'kpiRisco', 'rotulo' => 'Alunos em risco', 'valor' => (string) $ind['em_risco'], 'legenda' => 'presença abaixo de 75%', 'alerta' => $ind['em_risco'] > 0]) ?></div>
</div>

<section class="mb-4" aria-labelledby="tituloReceita">
    <h2 class="h5" id="tituloReceita">Receita dos últimos <?= esc((string) $meses_na_serie) ?> meses</h2>
    <div class="ct-card p-3 mb-2"><canvas id="graficoReceita" height="110" role="img" aria-label="Receita mensal de mensalidades e loja"></canvas></div>
    <div class="ct-card mb-3">
    <div class="table-responsive position-relative" tabindex="0" role="region" aria-label="Receita por mês">
        <table class="table table-striped table-hover align-middle mb-0">
            <caption class="visually-hidden">Receita por mês</caption>
            <thead><tr><th scope="col">Mês</th><th scope="col" class="num">Mensalidades</th><th scope="col" class="num">Loja</th><th scope="col" class="num">Total</th><th scope="col" class="num">Vendas na loja</th><th scope="col" class="num">Matrículas novas</th></tr></thead>
            <tbody>
                <?php foreach ($serie as $mes) : ?>
                    <tr>
                        <td><?= esc($mes['rotulo']) ?></td>
                        <td class="num"><?= esc(formatar_moeda($mes['mensalidades'])) ?></td>
                        <td class="num"><?= esc(formatar_moeda($mes['loja'])) ?></td>
                        <td class="num fw-semibold"><?= esc(formatar_moeda($mes['total'])) ?></td>
                        <td class="num"><?= esc((string) $mes['vendas']) ?></td>
                        <td class="num"><?= esc((string) $mes['novas']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    </div>
</section>

<div class="row g-4 mb-4">
    <section class="col-12 col-lg-6" aria-labelledby="tituloOcupacao">
        <h2 class="h5" id="tituloOcupacao">Ocupação por turma</h2>
        <div class="ct-card p-3 mb-2"><canvas id="graficoOcupacao" role="img" aria-label="Percentual de vagas ocupadas por turma"></canvas></div>
        <div class="ct-card">
        <div class="table-responsive position-relative" tabindex="0" role="region" aria-label="Ocupação por turma" style="max-height: 18rem;">
            <table class="table table-striped table-hover align-middle mb-0">
                <caption class="visually-hidden">Ocupação por turma</caption>
                <thead><tr><th scope="col">Turma</th><th scope="col" class="num">Alunos / vagas</th><th scope="col" class="num">Ocupação</th></tr></thead>
                <tbody>
                    <?php foreach ($turmas as $turma) : ?>
                        <tr>
                            <td><?= esc($turma['nome']) ?></td>
                            <td class="num"><?= esc($turma['ocupadas'] . ' / ' . $turma['vagas']) ?></td>
                            <td class="num"><?= esc($percentual($turma['percentual'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        </div>
    </section>

    <section class="col-12 col-lg-6" aria-labelledby="tituloModalidade">
        <h2 class="h5" id="tituloModalidade">Alunos por modalidade</h2>
        <div class="ct-card p-3 mb-2"><canvas id="graficoModalidade" role="img" aria-label="Alunos ativos por modalidade"></canvas></div>
        <h2 class="h5 mt-4" id="tituloFrequencia">Presença média por modalidade (últimos <?= esc((string) $janela_dias) ?> dias)</h2>
        <div class="ct-card p-3 mb-2"><canvas id="graficoFrequencia" role="img" aria-label="Presença média por modalidade, com a linha de 75%"></canvas></div>
        <div class="ct-card">
        <div class="table-responsive position-relative" tabindex="0" role="region" aria-label="Presença por modalidade">
            <table class="table table-striped table-hover align-middle mb-0">
                <caption class="visually-hidden">Presença média por modalidade</caption>
                <thead><tr><th scope="col">Modalidade</th><th scope="col" class="num">Alunos ativos</th><th scope="col" class="num">Presença</th></tr></thead>
                <tbody>
                    <?php $alunosPorNome = array_column($alunos_modalidade, 'alunos', 'modalidade'); ?>
                    <?php foreach ($frequencia as $linha) : ?>
                        <tr>
                            <td><?= esc($linha['modalidade']) ?></td>
                            <td class="num"><?= esc((string) ($alunosPorNome[$linha['modalidade']] ?? 0)) ?></td>
                            <td class="num"><?= esc($percentual($linha['percentual'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        </div>
    </section>
</div>

<div class="row g-4 mb-4">
    <section class="col-12 col-lg-6" aria-labelledby="tituloAtraso">
        <h2 class="h5" id="tituloAtraso">Inadimplência por tempo de atraso</h2>
        <div class="ct-card">
        <div class="table-responsive position-relative" tabindex="0" role="region" aria-label="Inadimplência por faixa de atraso">
            <table class="table table-striped table-hover align-middle mb-0">
                <caption class="visually-hidden">Mensalidades atrasadas por faixa de dias</caption>
                <thead><tr><th scope="col">Faixa</th><th scope="col" class="num">Mensalidades</th><th scope="col" class="num">Valor</th></tr></thead>
                <tbody>
                    <?php foreach ($atraso_faixas as $faixa) : ?>
                        <tr><td><?= esc($faixa['faixa']) ?></td><td class="num"><?= esc((string) $faixa['quantidade']) ?></td><td class="num"><?= esc(formatar_moeda($faixa['valor'])) ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        </div>
        <p class="small text-secondary mt-2 mb-0"><a href="<?= esc(url_to('mensalidades_inadimplentes'), 'html') ?>">Ver lista de inadimplentes</a></p>
    </section>

    <section class="col-12 col-lg-6" aria-labelledby="tituloPlanos">
        <h2 class="h5" id="tituloPlanos">Receita por plano (últimos <?= esc((string) $meses_na_serie) ?> meses)</h2>
        <div class="ct-card">
        <div class="table-responsive position-relative" tabindex="0" role="region" aria-label="Receita por plano">
            <table class="table table-striped table-hover align-middle mb-0">
                <caption class="visually-hidden">Receita de mensalidades pagas por plano</caption>
                <thead><tr><th scope="col">Plano</th><th scope="col" class="num">Alunos</th><th scope="col" class="num">Receita</th></tr></thead>
                <tbody>
                    <?php if ($receita_planos === []) : ?>
                        <tr><td colspan="3"><div class="ct-vazio"><p class="mb-0 fw-semibold">Sem mensalidades pagas no período.</p></div></td></tr>
                    <?php endif; ?>
                    <?php foreach ($receita_planos as $plano) : ?>
                        <tr><td><?= esc($plano['plano']) ?></td><td class="num"><?= esc((string) $plano['alunos']) ?></td><td class="num"><?= esc(formatar_moeda($plano['receita'])) ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        </div>
    </section>
</div>

<h2 class="h5 mb-3">Loja (últimos <?= esc((string) $janela_dias) ?> dias)</h2>
<div class="row g-4 mb-4">
    <section class="col-12 col-lg-6" aria-labelledby="tituloTopProdutos">
        <h3 class="h6" id="tituloTopProdutos">Produtos que mais faturaram</h3>
        <div class="ct-card">
        <div class="table-responsive position-relative" tabindex="0" role="region" aria-label="Produtos mais vendidos">
            <table class="table table-striped table-hover align-middle mb-0">
                <caption class="visually-hidden">Produtos que mais faturaram</caption>
                <thead><tr><th scope="col">Produto</th><th scope="col" class="num">Qtd.</th><th scope="col" class="num">Receita</th><th scope="col" class="num">Lucro</th></tr></thead>
                <tbody>
                    <?php if ($loja_top === []) : ?>
                        <tr><td colspan="4"><div class="ct-vazio"><p class="mb-0 fw-semibold">Sem vendas no período.</p></div></td></tr>
                    <?php endif; ?>
                    <?php foreach ($loja_top as $produto) : ?>
                        <tr>
                            <td><?= esc($produto['nome']) ?></td>
                            <td class="num"><?= esc((string) $produto['quantidade']) ?></td>
                            <td class="num"><?= esc(formatar_moeda($produto['receita'])) ?></td>
                            <td class="num"><?= esc(formatar_moeda($produto['lucro'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        </div>
    </section>

    <section class="col-12 col-lg-6" aria-labelledby="tituloCategorias">
        <h3 class="h6" id="tituloCategorias">Vendas por categoria</h3>
        <div class="ct-card">
        <div class="table-responsive position-relative" tabindex="0" role="region" aria-label="Vendas por categoria">
            <table class="table table-striped table-hover align-middle mb-0">
                <caption class="visually-hidden">Receita da loja por categoria de produto</caption>
                <thead><tr><th scope="col">Categoria</th><th scope="col" class="num">Unidades</th><th scope="col" class="num">Receita</th></tr></thead>
                <tbody>
                    <?php if ($loja_categorias === []) : ?>
                        <tr><td colspan="3"><div class="ct-vazio"><p class="mb-0 fw-semibold">Sem vendas no período.</p></div></td></tr>
                    <?php endif; ?>
                    <?php foreach ($loja_categorias as $categoria) : ?>
                        <tr>
                            <td><?= esc($categoriasProduto[$categoria['categoria']] ?? $categoria['categoria']) ?></td>
                            <td class="num"><?= esc((string) $categoria['quantidade']) ?></td>
                            <td class="num"><?= esc(formatar_moeda($categoria['receita'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        </div>
        <p class="small text-secondary mt-2 mb-0">
            Ticket médio no mês: <strong><?= esc(formatar_moeda($ind['ticket_loja'])) ?></strong> ·
            <?= esc((string) $ind['estoque_baixo']) ?> produto(s) com estoque baixo ·
            <a href="<?= esc(url_to('produtos'), 'html') ?>">Ver estoque</a>
        </p>
    </section>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(function () {
    // Sem internet a biblioteca não carrega: as tabelas acima continuam mostrando os mesmos números.
    if (typeof Chart === 'undefined') { return; }

    var dados = <?= $json($graficos) ?>;
    // Paleta só em cinza (sistema em preto e branco).
    var escuro = '#212529', medio = '#6c757d', claro = '#adb5bd';
    Chart.defaults.font.family = 'system-ui, sans-serif';
    Chart.defaults.color = '#212529';

    function criar(id, config) {
        var tela = document.getElementById(id);
        if (tela) { new Chart(tela, config); }
    }

    criar('graficoReceita', {
        type: 'bar',
        data: {
            labels: dados.receita.rotulos,
            datasets: [
                { label: 'Mensalidades', data: dados.receita.mensalidades, backgroundColor: escuro },
                { label: 'Loja', data: dados.receita.loja, backgroundColor: claro }
            ]
        },
        options: {
            responsive: true,
            scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true, ticks: { callback: function (v) { return 'R$ ' + v.toLocaleString('pt-BR'); } } } },
            plugins: { tooltip: { callbacks: { label: function (c) { return c.dataset.label + ': R$ ' + c.parsed.y.toLocaleString('pt-BR', { minimumFractionDigits: 2 }); } } } }
        }
    });

    criar('graficoOcupacao', {
        type: 'bar',
        data: { labels: dados.ocupacao.rotulos, datasets: [{ label: 'Ocupação (%)', data: dados.ocupacao.valores, backgroundColor: medio }] },
        options: { indexAxis: 'y', responsive: true, scales: { x: { beginAtZero: true, max: 100 } }, plugins: { legend: { display: false } } }
    });

    criar('graficoModalidade', {
        type: 'bar',
        data: { labels: dados.modalidade.rotulos, datasets: [{ label: 'Alunos ativos', data: dados.modalidade.valores, backgroundColor: escuro }] },
        options: { responsive: true, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }, plugins: { legend: { display: false } } }
    });

    criar('graficoFrequencia', {
        type: 'bar',
        data: {
            labels: dados.frequencia.rotulos,
            datasets: [
                { label: 'Presença (%)', data: dados.frequencia.valores, backgroundColor: medio },
                { label: 'Mínimo de 75%', data: dados.frequencia.rotulos.map(function () { return 75; }), type: 'line', borderColor: escuro, borderDash: [6, 4], pointRadius: 0, borderWidth: 2 }
            ]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true, max: 100 } } }
    });
})();
</script>
<?= $this->endSection() ?>
