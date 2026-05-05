<?php
include_once __DIR__ . "/conexao.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

$id_instituicao_raw = isset($_POST["id_instituicao"]) ? trim((string) $_POST["id_instituicao"]) : "";
$id_instituicao = ctype_digit($id_instituicao_raw) ? (int) $id_instituicao_raw : 0;
$tipo_escola = isset($_POST["tipo_escola"]) ? trim((string) $_POST["tipo_escola"]) : "";
$tipo = isset($_POST["tipo"]) ? trim((string) $_POST["tipo"]) : "";
$nome = isset($_POST["nome"]) ? trim((string) $_POST["nome"]) : "";
$capacidade_raw = isset($_POST["capacidade"]) ? trim((string) $_POST["capacidade"]) : "";
$longitude = isset($_POST["longitude"]) ? trim((string) $_POST["longitude"]) : "";
$latitude = isset($_POST["latitude"]) ? trim((string) $_POST["latitude"]) : "";

if ($id_instituicao <= 0 || $tipo_escola === "" || $tipo === "" || $nome === "" || $capacidade_raw === "" || !ctype_digit($capacidade_raw) || $longitude === "" || $latitude === "") {
    $retorno = [
        "status" => "not ok",
        "mensagem" => "Instituição, tipo de escola, tipo, nome, capacidade, longitude e latitude são obrigatórios.",
        "data" => [],
    ];
} else {
    $capacidade = (int) $capacidade_raw;
    $stmt = $conexao->prepare(
        "INSERT INTO Locais (id_instituicao, tipo_escola, tipo, nome, capacidade, longitude, latitude)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("isssiss", $id_instituicao, $tipo_escola, $tipo, $nome, $capacidade, $longitude, $latitude);

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
