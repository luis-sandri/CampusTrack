<?php
session_start();
include_once __DIR__ . "/../core/conexao.php";
require_once __DIR__ . "/../core/resposta.php";
require_once __DIR__ . "/validacoes.php";
require_once __DIR__ . "/repositorio.php";

$erros = [];
$login = autenticacao_ler_login($_POST, $erros);

if (count($erros) > 0) {
    responder_json(resposta_validacao($erros));
}

$organizador = autenticacao_buscar_organizador_por_email($conexao, $login["email"]);

if ($organizador === null) {
    responder_json(resposta_erro("Nao foi possivel validar o acesso."));
}

if (count($organizador) === 0 || !password_verify($login["senha"], $organizador["senha"])) {
    responder_json(resposta_erro("E-mail ou senha invalidos."));
}

session_regenerate_id(true);

$_SESSION["organizador_logado"] = true;
$_SESSION["organizador_id"] = $organizador["id_organizador"];
$_SESSION["organizador_usuario_id"] = $organizador["id_usuario"];
$_SESSION["organizador_id_organizacao"] = $organizador["id_organizacao"];
$_SESSION["organizador_nome"] = $organizador["nome"];
$_SESSION["organizador_email"] = $organizador["email"];
$_SESSION["ultima_atividade"] = time();

unset($organizador["senha"]);
responder_json(resposta_ok("Acesso validado com sucesso.", [$organizador]));
