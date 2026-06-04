# Manutencao de CRUDs

Este projeto usa uma separacao simples para facilitar manutencao:

- HTML define os campos e o atributo `name`.
- JS carrega dados, preenche selects e envia o formulario.
- PHP valida os dados recebidos.
- Repositorio PHP executa SQL.
- Endpoint PHP apenas orquestra validacao, repositorio e resposta JSON.

## Como adicionar um campo

Exemplo: adicionar `descricao` em um CRUD.

1. Atualize o banco:

```sql
ALTER TABLE NomeDaTabela ADD descricao VARCHAR(255) NOT NULL;
```

2. Adicione o campo no HTML:

```html
<input type="text" id="entidade-descricao" name="descricao" required>
```

O `name` e o nome lido no PHP devem ser iguais.

3. Leia e valide em `src/php/<dominio>/validacoes.php`:

```php
"descricao" => campo_texto_obrigatorio($origem, "descricao", "Descricao", $erros),
```

4. Inclua no `INSERT` e no `UPDATE` em `src/php/<dominio>/repositorio.php`.

5. Inclua no `SELECT` se o campo aparece na tela de edicao ou listagem.

6. Na tela de edicao, preencha o campo no JS:

```js
document.getElementById("entidade-descricao").value = reg.descricao;
```

Para tela de cadastro, normalmente nao precisa alterar o JS se o formulario usa `CampusTrack.form.enviar(form, url)`, porque `FormData(form)` envia todos os campos com `name`.

## Padrao de arquivos

```text
src/php/<dominio>/validacoes.php
src/php/<dominio>/repositorio.php
src/php/<dominio>/adicionar.php
src/php/<dominio>/alterar.php
src/php/<dominio>/excluir.php
src/php/<dominio>/get.php
src/js/<dominio>/adicionar.js
src/js/<dominio>/alterar.js
src/html/<perfil-ou-dominio>/<tela>.html
```

Use `src/html/visitante` apenas para telas publicas. Telas autenticadas devem ficar no perfil dono do fluxo, por exemplo `src/html/organizacao` e `src/html/organizador`.

Arquivos compartilhados do PHP ficam em `src/php/core`. Endpoints de login ficam em `src/php/autenticacao`.

Helpers globais ficam em `src/js/shared/formulario.js` dentro do namespace `CampusTrack`.
Validacao de sessao no front fica em `src/js/shared/sessao.js`.

- `CampusTrack.api.json(url, options)` chama PHP e trata resposta vazia/invalida.
- `CampusTrack.form.enviar(form, url)` envia formulario.
- `CampusTrack.resposta.mensagem(resposta)` le a mensagem padronizada.
- `CampusTrack.dom.escapeHtml(valor)` protege HTML montado por string.

## Regra principal

Nao esconda erro convertendo dado invalido para `0`, `""` ou `[]`.

Quando algo estiver errado, retorne uma resposta clara:

```php
responder_json(resposta_erro("Mensagem explicando o problema."));
```
