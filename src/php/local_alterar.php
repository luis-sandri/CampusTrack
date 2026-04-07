<?php
include_once __DIR__ . "/conexao.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

if (isset($_GET["id"])) {
    $id = (int) $_GET["id"];
    $id_instituicao = isset($_POST["id_instituicao"]) ? (int) $_POST["id_instituicao"] : 0;
    $tipo = isset($_POST["tipo"]) ? trim((string) $_POST["tipo"]) : "";
    $nome = isset($_POST["nome"]) ? trim((string) $_POST["nome"]) : "";
    $capacidade_raw = isset($_POST["capacidade"]) ? trim((string) $_POST["capacidade"]) : "";
    $longitude = isset($_POST["longitude"]) ? trim((string) $_POST["longitude"]) : "";
    $latitude = isset($_POST["latitude"]) ? trim((string) $_POST["latitude"]) : "";

    if ($id <= 0 || $id_instituicao <= 0 || $nome === "") {
        $retorno = [
            "status" => "not ok",
            "mensagem" => "Dados inválidos.",
            "data" => [],
        ];
    } else {
        if ($capacidade_raw === "") {
            $stmt = $conexao->prepare(
                "UPDATE Locais SET id_instituicao = ?, tipo = ?, nome = ?, capacidade = NULL, longitude = ?, latitude = ?
                 WHERE id_local = ?"
            );
            $stmt->bind_param("issssi", $id_instituicao, $tipo, $nome, $longitude, $latitude, $id);
        } else {
            $capacidade = (int) $capacidade_raw;
            $stmt = $conexao->prepare(
                "UPDATE Locais SET id_instituicao = ?, tipo = ?, nome = ?, capacidade = ?, longitude = ?, latitude = ?
                 WHERE id_local = ?"
            );
            $stmt->bind_param("isisssi", $id_instituicao, $tipo, $nome, $capacidade, $longitude, $latitude, $id);
        }

        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $retorno = [
                "status" => "ok",
                "mensagem" => "Registro alterado com sucesso.",
                "data" => [],
            ];
        } else {
            $retorno = [
                "status" => "not ok",
                "mensagem" => "Não foi possível alterar o registro.",
                "data" => [],
            ];
        }

        $stmt->close();
    }
} else {
    $retorno = [
        "status" => "not ok",
        "mensagem" => "Não foi possível alterar o registro sem ID.",
        "data" => [],
    ];
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
