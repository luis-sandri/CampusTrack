<?php
session_start();
include_once __DIR__ . "/../core/conexao.php";
require_once __DIR__ . "/../core/resposta.php";
require_once __DIR__ . "/../organizacoes/validacoes.php";
require_once __DIR__ . "/../organizacoes/repositorio.php";

$erros = [];
$login = organizacao_ler_login($_POST, $erros);

if (count($erros) > 0) {
    responder_json(resposta_validacao($erros));
}

$organizacao = organizacao_buscar_por_cnpj($conexao, $login["cnpj"]);

if ($organizacao === null) {
    responder_json(resposta_erro("Nao foi possivel validar o acesso."));
}

if (count($organizacao) === 0 || !password_verify($login["senha"], $organizacao["senha"])) {
    responder_json(resposta_erro("CNPJ ou senha invalidos."));
}

session_regenerate_id(true);

$_SESSION["organizacao_logada"] = true;
$_SESSION["organizacao_id"] = $organizacao["id_organizacao"];
$_SESSION["organizacao_nome"] = $organizacao["nome"];
$_SESSION["organizacao_cnpj"] = $organizacao["cnpj"];
$_SESSION["ultima_atividade"] = time();

unset($organizacao["senha"]);
responder_json(resposta_ok("Acesso validado com sucesso.", [$organizacao]));
