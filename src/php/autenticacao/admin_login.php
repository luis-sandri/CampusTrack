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

$admin = autenticacao_buscar_admin_por_email($conexao, $login["email"]);

if ($admin === null) {
    responder_json(resposta_erro("Nao foi possivel validar o acesso."));
}

if (count($admin) === 0 || !password_verify($login["senha"], $admin["senha"])) {
    responder_json(resposta_erro("E-mail ou senha invalidos."));
}

session_regenerate_id(true);

$_SESSION["admin_logado"] = true;
$_SESSION["admin_id"] = $admin["id_usuario"];
$_SESSION["admin_email"] = $admin["email"];
$_SESSION["admin_nome"] = $admin["nome"];
$_SESSION["ultima_atividade"] = time();

responder_json(resposta_ok("Acesso validado com sucesso."));
