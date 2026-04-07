<?php
$host = "127.0.0.1";
$user = "root";
$pass = "";
$dbname = "campustrack";

$conexao = new mysqli($host, $user, $pass, $dbname);

if ($conexao->connect_error) {
    header("Content-type:application/json;charset=utf-8");
    echo json_encode([
        "status" => "not ok",
        "mensagem" => "Falha na conexão com o banco.",
        "data" => [],
    ]);
    exit;
}

$conexao->set_charset("utf8mb4");
