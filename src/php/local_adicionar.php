<?php
include_once __DIR__ . "/conexao.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

$id_instituicao = isset($_POST["id_instituicao"]) ? (int) $_POST["id_instituicao"] : 0;
$tipo = isset($_POST["tipo"]) ? trim((string) $_POST["tipo"]) : "";
$nome = isset($_POST["nome"]) ? trim((string) $_POST["nome"]) : "";
$capacidade_raw = isset($_POST["capacidade"]) ? trim((string) $_POST["capacidade"]) : "";
$longitude = isset($_POST["longitude"]) ? trim((string) $_POST["longitude"]) : "";
$latitude = isset($_POST["latitude"]) ? trim((string) $_POST["latitude"]) : "";

if ($id_instituicao <= 0 || $nome === "") {
    $retorno = [
        "status" => "not ok",
        "mensagem" => "Instituição e nome são obrigatórios.",
        "data" => [],
    ];
} else {
    if ($capacidade_raw === "") {
        $stmt = $conexao->prepare(
            "INSERT INTO Locais (id_instituicao, tipo, nome, capacidade, longitude, latitude)
             VALUES (?, ?, ?, NULL, ?, ?)"
        );
        $stmt->bind_param("issss", $id_instituicao, $tipo, $nome, $longitude, $latitude);
    } else {
        $capacidade = (int) $capacidade_raw;
        $stmt = $conexao->prepare(
            "INSERT INTO Locais (id_instituicao, tipo, nome, capacidade, longitude, latitude)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("isisss", $id_instituicao, $tipo, $nome, $capacidade, $longitude, $latitude);
    }

    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $retorno = [
            "status" => "ok",
            "mensagem" => "Registro cadastrado com sucesso.",
            "data" => [["id_local" => (int) $conexao->insert_id]],
        ];
    } else {
        $retorno = [
            "status" => "not ok",
            "mensagem" => "Não foi possível cadastrar o registro.",
            "data" => [],
        ];
    }

    $stmt->close();
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
