<?php
session_start();

if (!isset($_SESSION["admin_logado"]) || $_SESSION["admin_logado"] !== true) {
    header("Content-type:application/json;charset=utf-8");
    echo json_encode([
        "status" => "not ok",
        "mensagem" => "Acesso negado.",
        "data" => [],
    ]);
    exit;
}
