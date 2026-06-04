<?php
include_once __DIR__ . "/../core/valida_sessao_aluno.php";
include_once __DIR__ . "/../core/conexao.php";
require_once __DIR__ . "/../core/resposta.php";
require_once __DIR__ . "/validacoes.php";
require_once __DIR__ . "/repositorio.php";

$erros = [];
$id_local = favorito_ler_id_local($_POST, $erros);
$id_aluno = (int) $_SESSION["aluno_id"];

if (count($erros) > 0) {
    responder_json(resposta_validacao($erros));
}

$local_existe = favorito_local_existe($conexao, $id_local);

if ($local_existe === null) {
    responder_json(resposta_erro("Nao foi possivel validar o local."));
}

if (!$local_existe) {
    responder_json(resposta_erro("Local nao encontrado."));
}

$favorito_existe = favorito_existe($conexao, $id_aluno, $id_local);

if ($favorito_existe === null) {
    responder_json(resposta_erro("Nao foi possivel verificar o favorito."));
}

if ($favorito_existe) {
    responder_json(resposta_ok("Local ja esta nos favoritos."));
}

$id_favorito = favorito_inserir($conexao, $id_aluno, $id_local);

if ($id_favorito <= 0) {
    responder_json(resposta_erro("Nao foi possivel adicionar o favorito."));
}

responder_json(resposta_ok("Local adicionado aos favoritos.", [["id_favorito" => $id_favorito]]));
