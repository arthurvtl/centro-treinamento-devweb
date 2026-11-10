# Deploy (publicação online)

O sistema roda online com custo zero:

| Peça | Serviço | Plano |
|---|---|---|
| Aplicação (PHP 8.3 + Apache) | [Render](https://render.com), serviço web com Docker | Free |
| Banco MySQL | [Aiven](https://aiven.io), serviço `mysql-ct` | Free (1 GB) |

## Como funciona

- O `Dockerfile` da raiz monta a imagem com PHP 8.3, Apache e as extensões do CodeIgniter (`intl`, `mysqli`), instala as dependências com Composer e expõe só a pasta `public/` (`docker/apache-site.conf`).
- Ao iniciar, o contêiner executa `docker/iniciar.sh`:
  1. grava o certificado CA do Aiven (variável `DB_SSL_CA_PEM`), porque o banco só aceita conexão com SSL;
  2. aplica as migrations (`php spark migrate --all`);
  3. se a tabela de usuários estiver vazia (`docker/banco-vazio.php`), roda os seeders de demonstração;
  4. sobe o Apache na porta definida pelo Render.
- `app/Config/Database.php` ativa o SSL quando a variável `DB_SSL_CA` existe. Localmente ela não existe e nada muda.
- Cada push na `main` gera um novo deploy automático no Render.

## Variáveis de ambiente (painel do Render → Environment)

As credenciais ficam só no painel do Render, nunca no código (mesma regra do `.env` local).

| Variável | Valor |
|---|---|
| `CI_ENVIRONMENT` | `production` |
| `app_baseURL` | URL pública do serviço, com `/` no fim |
| `database_default_hostname` | host do Aiven |
| `database_default_port` | porta do Aiven |
| `database_default_database` | `defaultdb` |
| `database_default_username` | usuário do Aiven |
| `database_default_password` | senha do Aiven |
| `database_default_DBDriver` | `MySQLi` |
| `DB_SSL_CA_PEM` | conteúdo do certificado CA do Aiven (Overview → CA certificate) |
| `RECRIAR_BANCO` | `0` (use `1` em um deploy para apagar e recriar o banco com os dados de demonstração; depois volte para `0`) |

## Plano gratuito: cuidados

- O Render **hiberna** o serviço após 15 minutos sem acesso; o primeiro acesso depois disso leva de 30 a 60 segundos. Antes de uma apresentação, abra o link alguns minutos antes.
- O Aiven pode **desligar** um banco gratuito parado por muito tempo. Confira no painel do Aiven se o serviço `mysql-ct` está *Running* alguns dias antes da apresentação.
