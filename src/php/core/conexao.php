<?php
require_once __DIR__ . "/resposta.php";

$host = "127.0.0.1";
$user = "root";
$pass = "";
$dbname = "campustrack";

$conexao = new mysqli($host, $user, $pass, $dbname);

if ($conexao->connect_error) {
    responder_json(resposta_erro("Falha na conexao com o banco."));
}

$conexao->set_charset("utf8mb4");
