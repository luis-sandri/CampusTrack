# CampusTrack

CampusTrack é um sistema web para gerenciamento de instituições, gerentes de locais e espaços físicos de campus. O projeto foi desenvolvido com páginas HTML, scripts JavaScript e endpoints PHP, usando MySQL como banco de dados. Inclui visualização interativa de mapas do campus e gerenciamento de eventos e favoritos. 

## Funcionalidades

- Seleção de instituição na tela inicial.
- Login de administrador.
- Login de gerente de blocos/locais.
- Login de organização.
- Cadastro de organização.
- Login de organizador.
- Cadastro, edição, listagem e exclusão de organizadores por organização.
- Cadastro de estudante com envio e verificação de código por e-mail.
- Cadastro, edição, listagem e exclusão de instituições.
- Cadastro, edição, listagem e exclusão de gerentes.
- Cadastro, edição, listagem e exclusão de locais.
- Controle de sessão para áreas administrativas.

## Tecnologias

- HTML5
- CSS/Bootstrap 5
- JavaScript
- PHP
- MySQL
- PHPMailer
- XAMPP ou ambiente equivalente com Apache, PHP e MySQL

## Estrutura do projeto

```text
CampusTrack/
|-- assets/                 # Imagens e arquivos estáticos
|-- database/               # Script SQL e modelos do banco
|   `-- campustrack.sql
`-- src/
    |-- html/               # Telas do sistema
    |   |-- admin/
    |   |-- estudante/
    |   |-- gerente/
    |   `-- visitante/
    |-- js/                 # Scripts das telas
    `-- php/                # Endpoints e conexão com o banco
        `-- libs/PHPMailer/
```

## Requisitos

- XAMPP instalado, ou outro ambiente com Apache, PHP e MySQL.
- MySQL em execução.
- Navegador moderno.

## Como executar

1. Coloque o projeto dentro da pasta `htdocs` do XAMPP:

```text
C:\xampp\htdocs\CampusTrack
```

2. Inicie o Apache e o MySQL pelo painel do XAMPP.

3. Importe o banco de dados:

- Abra o phpMyAdmin.
- Execute o arquivo `database/campustrack.sql`.
- O script cria o banco `campustrack` e suas tabelas.

4. Confira a conexão em `src/php/conexao.php`:

```php
$host = "127.0.0.1";
$user = "root";
$pass = "";
$dbname = "campustrack";
```

5. Acesse o sistema no navegador:

```text
http://localhost/CampusTrack/src/html/index.html
```

## Páginas principais

- Início: `src/html/index.html`
- Login do administrador: `src/html/admin/login.html`
- Login do gerente: `src/html/gerente/login.html`
- Login da organização: `src/html/visitante/login.html?tipo=organizacao`
- Login do organizador: `src/html/visitante/organizador_login.html`
- Gerenciar organizadores: `src/html/visitante/gerenciar_organizador.html`
- Cadastro da organização: `src/html/visitante/organizacao_cadastro.html`
- Cadastro do estudante: `src/html/estudante/login.html`
- Gerenciar instituições: `src/html/admin/gerenciar_instituicao.html`
- Gerenciar gerentes: `src/html/admin/gerenciar_gerente.html`
- Gerenciar locais: `src/html/gerente/gerenciar_local.html`

## Banco de dados

O modelo físico está no arquivo `database/campustrack.sql`. As principais tabelas são:

- `Usuario`
- `Instituicao`
- `Aluno`
- `Gerente_Locais`
- `Administrador`
- `Locais`
- `Organizacao`
- `Organizador`
- `Evento`

## Dados de teste

O arquivo `database/campustrack.sql` inclui uma populacao simples para testes:

- Administrador: `admin@campustrack.com` / `Teste@123`
- Gerente: `gerente@campustrack.com` / `Teste@123`
- Aluno: `aluno@pucpr.edu.br` / `Teste@123`
- Organizacao: CNPJ `11222333000181` / senha `Teste@123`
- Organizador: `organizador@campustrack.com` / `Teste@123`

## Configuração de e-mail

O envio de código para cadastro de estudante usa PHPMailer em `src/php/codigo_enviar.php`.

Antes de publicar ou compartilhar o projeto, mova as credenciais SMTP para variáveis de ambiente ou outro arquivo de configuração fora do versionamento. Manter usuário e senha diretamente no código não é recomendado.

## Observações

- Os modelos conceitual e lógico do banco estão disponíveis na pasta `database/`.
