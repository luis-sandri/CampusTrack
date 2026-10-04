<?php

declare(strict_types=1);

const TEST_DB_HOST = "127.0.0.1";
const TEST_DB_USER = "root";
const TEST_DB_PASSWORD = "";
const TEST_DB_NAME = "campustrack_test";
const TEST_DB_PORT = 3306;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function criar_conexao_teste(): mysqli
{
    $host = getenv("CAMPUS_TRACK_TEST_DB_HOST");
    $host = $host === false || $host === "" ? TEST_DB_HOST : $host;

    $user = getenv("CAMPUS_TRACK_TEST_DB_USER");
    $user = $user === false || $user === "" ? TEST_DB_USER : $user;

    $password = getenv("CAMPUS_TRACK_TEST_DB_PASSWORD");
    $password = $password === false ? TEST_DB_PASSWORD : $password;

    $port = getenv("CAMPUS_TRACK_TEST_DB_PORT");
    $port = $port === false || $port === "" ? TEST_DB_PORT : filter_var($port, FILTER_VALIDATE_INT);

    if ($port === false || $port < 1 || $port > 65535) {
        throw new RuntimeException("A porta do banco de teste deve estar entre 1 e 65535.");
    }

    $conexao = new mysqli(
        $host,
        $user,
        $password,
        TEST_DB_NAME,
        $port
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

