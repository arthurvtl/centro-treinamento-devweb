# Sistema CT — Gestão de Centro de Treinamento

Sistema web para centros de treinamento de lutas e esportes coletivos. Substitui planilhas e mensagens por um cadastro único de alunos, professores, turmas, matrículas, frequência e mensalidades, aplicando as regras de negócio do centro (lotação por categoria, professor habilitado, menor de idade com responsável, certificado por frequência e adimplência).

Projeto da disciplina de Desenvolvimento Web (IFES). A análise de negócio completa está em [`docs/analise/documento-analise.md`](docs/analise/documento-analise.md).

## Integrantes

- ARTHUR MARQUES
- ARTHUR VITAL
- GABRIEL ABREU
- INÁCIO ALMEIDA
- JOÃO PAULO CASTANHEIRA
- PEDRO GRONER
- VITOR FREIRE

## Tecnologias

- PHP 8.2 ou superior (desenvolvido e testado com PHP 8.3)
- CodeIgniter 4 (v4.7), no padrão MVC
- MySQL 8
- Bootstrap 5.3 (via CDN jsdelivr, versão fixa) e HTML semântico
- Composer para as dependências

## Requisitos

- PHP 8.2+ com as extensões `intl`, `mbstring`, `mysqlnd` (mysqli) e `curl`
- Composer 2
- MySQL 8 (ou MariaDB compatível) em execução
- Navegador atualizado (Chrome, Edge, Firefox ou Safari)

## Instalação

```bash
git clone https://github.com/arthurvtl/centro-treinamento-devweb.git
cd centro-treinamento-devweb
composer install
cp env .env
```

Edite o arquivo `.env` e ajuste as credenciais do banco. Elas ficam **somente** no `.env`, que não é versionado:

```ini
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost:8080/'

database.default.hostname = 127.0.0.1
database.default.database = ct_devweb
database.default.username = seu_usuario
database.default.password = sua_senha
database.default.DBDriver = MySQLi
database.default.port = 3306
```

## Criar o banco de dados

Com o MySQL ligado, crie o banco vazio (uma vez) com um usuário que tenha permissão de criar bancos e dê ao usuário do `.env` acesso a ele:

```bash
mysql -u root -p -e "CREATE DATABASE ct_devweb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Depois aplique as migrations (12 tabelas do DER) e carregue os dados de demonstração:

```bash
php spark migrate
php spark db:seed DatabaseSeeder
```

Para recomeçar do zero: `php spark migrate:refresh -f && php spark db:seed DatabaseSeeder`.

## Execução

```bash
php spark serve
```

Abra <http://localhost:8080> no navegador.

## Logins de demonstração

| Perfil | E-mail | Senha |
|---|---|---|
| Proprietário | `proprietario@ct.local` | `ct123456` |
| Recepcionista | `recepcao@ct.local` | `ct123456` |

Os dados de demonstração incluem 4 modalidades (lutas com capacidade 20 e esportes coletivos com 25), 3 professores, 3 turmas, 4 alunos (um deles menor de idade, com responsável), 2 planos e mensalidades paga, em aberto e atrasada.

## Permissões por perfil (RN05)

| Área | Proprietário | Recepcionista |
|---|:-:|:-:|
| Alunos (cadastro, busca, inativar) | Sim | Sim (sem ver a condição de saúde, LGPD) |
| Excluir aluno | Sim | Não |
| Matrículas, troca de turma e cancelamento | Sim | Sim |
| Turmas: consultar | Sim | Sim |
| Turmas: criar, editar, suspender, encerrar | Sim | Não |
| Frequência (chamada) | Sim | Sim |
| Professores e modalidades | Sim | Não |
| Financeiro do aluno, vincular plano e dar baixa em mensalidade | Sim | Sim |
| Planos, lista de mensalidades, inadimplentes, gerar mensalidade | Sim | Não |
| Relatórios operacionais (alunos, modalidades, vagas) | Sim | Sim |
| Relatório financeiro mensal | Sim | Não |

A autorização é conferida no servidor pelos filtros de rota, e não apenas escondendo botões.

## Módulos e regras implementadas

| Módulo | Requisitos | Regras |
|---|---|---|
| Acesso (login, logout, perfis) | RF20, RF21 | RN05 |
| Alunos e responsável legal | RF01, RF02, RF03 | RN04, RN06 |
| Professores e modalidades | RF04, RF05 | RN01, RN02, RN03 |
| Turmas (suspender, reativar, encerrar) | RF06, RF22, RF23 | RN03, RN09, RN10 |
| Matrículas e troca de turma | RF07, RF08, RF09 | RN01, RN02, RN04, RN10 |
| Frequência e alunos abaixo de 75% | RF10, RF11 | RN09, RN10 |
| Planos, mensalidades e inadimplência | RF12 a RF15, RF24, RF25 | RN07, RN08, RN11 |
| Relatórios | RF16 a RF19 | RN05 |

## Estrutura do projeto

O código segue o MVC do CodeIgniter 4, com Controllers enxutos:

```text
app/
├── Config/           rotas agrupadas por módulo (Routes.php) e filtros (Filters.php)
├── Controllers/      recebem a requisição e devolvem a View
├── Database/
│   ├── Migrations/   12 tabelas do DER
│   └── Seeds/        DatabaseSeeder e dados de demonstração
├── Filters/          AuthFilter (sessão) e PerfilFilter (proprietario | recepcionista)
├── Helpers/          menu por perfil e formatação (CPF, telefone, data, moeda)
├── Models/           allowedFields, validação em PT-BR e consultas
├── Services/         fluxos que envolvem várias tabelas (matrícula, encerramento, pagamento)
├── Validation/       regras próprias (cpf_valido, data_nao_futura, regras de turma e modalidade)
└── Views/            Bootstrap 5, HTML semântico e esc() em toda saída dinâmica
```

## Segurança

- Proteção CSRF ativa em todas as requisições (`csrf_field()` em todo formulário).
- Alterações e exclusões somente por POST; leitura por GET.
- Senhas com `password_hash`; credenciais do banco apenas no `.env`.
- Saída escapada com `esc()`; condição de saúde (dado sensível, LGPD) visível só ao proprietário.

## Fora do escopo desta versão

Integração bancária, cobrança automática por WhatsApp, aplicativo nativo, gateway de pagamento e multiempresa (itens "Won't have" da matriz MoSCoW, seção 10 do documento de análise).
