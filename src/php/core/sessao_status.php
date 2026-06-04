<?php
include_once __DIR__ . "/sessao.php";

$perfil = isset($_GET["perfil"]) ? trim((string) $_GET["perfil"]) : "";
$dados = $perfil !== "" ? dados_sessao_por_perfil($perfil) : null;

if ($dados === null) {
    responder_json(resposta_erro("Sessao expirada ou acesso negado."));
}

responder_json(resposta_ok("Sessao ativa.", [$dados]));
