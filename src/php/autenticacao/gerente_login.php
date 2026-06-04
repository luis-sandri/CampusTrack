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

$gerente = autenticacao_buscar_gerente_por_email($conexao, $login["email"]);

if ($gerente === null) {
    responder_json(resposta_erro("Nao foi possivel validar o acesso."));
}

if (count($gerente) === 0 || !password_verify($login["senha"], $gerente["senha"])) {
    responder_json(resposta_erro("E-mail ou senha invalidos."));
}

session_regenerate_id(true);

$_SESSION["gerente_logado"] = true;
$_SESSION["gerente_id"] = $gerente["id_usuario"];
$_SESSION["gerente_email"] = $gerente["email"];
$_SESSION["gerente_nome"] = $gerente["nome"];
$_SESSION["gerente_id_instituicao"] = $gerente["id_instituicao"];
$_SESSION["ultima_atividade"] = time();

responder_json(resposta_ok("Acesso validado com sucesso."));
