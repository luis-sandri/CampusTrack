<?php
include_once __DIR__ . "/../core/valida_sessao_organizacao.php";
include_once __DIR__ . "/../core/conexao.php";
require_once __DIR__ . "/../core/resposta.php";
require_once __DIR__ . "/validacoes.php";
require_once __DIR__ . "/repositorio.php";

$id_organizacao = (int) $_SESSION["organizacao_id"];
$erros = [];
$organizador = organizador_ler_cadastro($_POST, $erros);

if (count($erros) > 0) {
    responder_json(resposta_validacao($erros));
}

if (usuario_email_em_uso($conexao, $organizador["email"])) {
    responder_json(resposta_erro("Ja existe um usuario cadastrado com este e-mail."));
}

$id_usuario = organizador_inserir($conexao, $id_organizacao, $organizador);

if ($id_usuario <= 0) {
    responder_json(resposta_erro("Nao foi possivel cadastrar o organizador."));
}

responder_json(resposta_ok("Organizador cadastrado com sucesso.", [["id_usuario" => $id_usuario]]));
