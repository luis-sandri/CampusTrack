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

if (!favorito_remover($conexao, $id_aluno, $id_local)) {
    responder_json(resposta_erro("Favorito nao encontrado."));
}

responder_json(resposta_ok("Local removido dos favoritos."));
