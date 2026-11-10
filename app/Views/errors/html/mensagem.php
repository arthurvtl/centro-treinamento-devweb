<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title><?= esc($titulo) ?> · Sistema CT</title>
    <style>
        /* Página independente do Bootstrap: continua legível mesmo sem a CDN. */
        body { margin: 0; background: #f5f6f8; color: #1a1d21; font: 1rem/1.6 system-ui, sans-serif; }
        main { max-width: 40rem; margin: 10vh auto; padding: 2rem; background: #fff; border: 1px solid #c9cdd2; border-radius: .5rem; }
        h1 { font-size: 1.75rem; line-height: 1.3; }
        a { color: #1f2937; }
        a:focus-visible { outline: 2px solid #1a1d21; outline-offset: 3px; }
        @media (max-width: 44rem) { main { margin: 2rem 1rem; padding: 1.5rem; } }
    </style>
</head>
<body>
    <main>
        <h1><?= esc($titulo) ?></h1>
        <p><?= esc($descricao) ?></p>
        <a href="<?= esc(site_url(), 'attr') ?>">Voltar ao início</a>
    </main>
</body>
</html>
