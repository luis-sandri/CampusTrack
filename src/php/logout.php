<?php
include_once __DIR__ . "/sessao.php";

encerrar_sessao();

header("Content-type:application/json;charset=utf-8");
echo json_encode([
    "status" => "ok",
    "mensagem" => "Sessao encerrada.",
    "data" => [],
]);
