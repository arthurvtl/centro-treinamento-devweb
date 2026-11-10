#!/usr/bin/env bash
# Inicialização do contêiner no Render:
# 1) grava o certificado CA do banco (vem da variável DB_SSL_CA_PEM) para a conexão SSL;
# 2) ajusta a porta do Apache à porta definida pelo Render (variável PORT);
# 3) aplica as migrations e, se o banco estiver vazio (ou RECRIAR_BANCO=1), roda os seeders;
# 4) sobe o Apache.
set -euo pipefail
cd /var/www/html

export PORT="${PORT:-10000}"
echo "Listen ${PORT}" > /etc/apache2/ports.conf

if [ -n "${DB_SSL_CA_PEM:-}" ]; then
    printf '%s\n' "$DB_SSL_CA_PEM" > /tmp/ca-banco.pem
    export DB_SSL_CA=/tmp/ca-banco.pem
fi

if [ "${RECRIAR_BANCO:-0}" = "1" ]; then
    echo "› Recriando o banco do zero (RECRIAR_BANCO=1)"
    php spark migrate:refresh --all -f
    php spark db:seed DatabaseSeeder
else
    php spark migrate --all
    # Popula com os dados de demonstração só na primeira subida (tabela de usuários vazia).
    if php docker/banco-vazio.php; then
        echo "› Banco vazio: rodando os seeders"
        php spark db:seed DatabaseSeeder
    fi
fi

chown -R www-data:www-data writable
exec apache2-foreground
