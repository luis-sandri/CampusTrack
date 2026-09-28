<?php

declare(strict_types=1);

const TEST_DB_HOST = "127.0.0.1";
const TEST_DB_USER = "root";
const TEST_DB_PASSWORD = "";
const TEST_DB_NAME = "campustrack_test";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function criar_conexao_teste(): mysqli
{
    $conexao = new mysqli(
        TEST_DB_HOST,
        TEST_DB_USER,
        TEST_DB_PASSWORD,
        TEST_DB_NAME
    );
    $conexao->set_charset("utf8mb4");

    $resultado = $conexao->query("SELECT DATABASE() AS banco_atual");
    $banco_atual = $resultado->fetch_assoc()["banco_atual"] ?? "";

    if ($banco_atual !== TEST_DB_NAME) {
        $conexao->close();
        throw new RuntimeException("A suite deve usar exclusivamente o banco campustrack_test.");
    }

    return $conexao;
}

