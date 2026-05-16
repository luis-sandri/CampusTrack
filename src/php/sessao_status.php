<?php
include_once __DIR__ . "/sessao.php";

$perfil = isset($_GET["perfil"]) ? trim((string) $_GET["perfil"]) : "";
$dados = $perfil !== "" ? dados_sessao_por_perfil($perfil) : null;

header("Content-type:application/json;charset=utf-8");

if ($dados === null) {
    echo json_encode([
        "status" => "not ok",
        "mensagem" => "Sessao expirada ou acesso negado.",
        "data" => [],
    ]);
    exit;
}

echo json_encode([
    "status" => "ok",
    "mensagem" => "Sessao ativa.",
    "data" => [$dados],
]);
