<?php
session_start();

if (!isset($_SESSION["organizacao_logada"]) || $_SESSION["organizacao_logada"] !== true) {
    header("Content-type:application/json;charset=utf-8");
    echo json_encode([
        "status" => "not ok",
        "mensagem" => "Acesso negado.",
        "data" => [],
    ]);
    exit;
}

