<?php
include_once __DIR__ . "/valida_sessao_admin.php";
include_once __DIR__ . "/conexao.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

$id_raw = isset($_GET["id"]) ? trim((string) $_GET["id"]) : "";
$id = ctype_digit($id_raw) ? (int) $id_raw : 0;
$id_instituicao_raw = isset($_POST["id_instituicao"]) ? trim((string) $_POST["id_instituicao"]) : "";
$id_instituicao = ctype_digit($id_instituicao_raw) ? (int) $id_instituicao_raw : 0;
$nome = isset($_POST["nome"]) ? trim((string) $_POST["nome"]) : "";
$longitude = isset($_POST["longitude"]) ? trim((string) $_POST["longitude"]) : "";
$latitude = isset($_POST["latitude"]) ? trim((string) $_POST["latitude"]) : "";

if ($id <= 0 || $id_instituicao <= 0 || $nome === "" || $longitude === "" || $latitude === "" || !is_numeric($longitude) || !is_numeric($latitude)) {
    $retorno = [
        "status" => "not ok",
        "mensagem" => "Dados invalidos.",
        "data" => [],
    ];
} else {
    $stmt = $conexao->prepare("UPDATE Mapa_No SET nome = ?, longitude = ?, latitude = ? WHERE id_no = ? AND id_instituicao = ?");
    $stmt->bind_param("sssii", $nome, $longitude, $latitude, $id, $id_instituicao);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $retorno = [
            "status" => "ok",
            "mensagem" => "No alterado com sucesso.",
            "data" => [],
        ];
    } else {
        $retorno = [
            "status" => "not ok",
            "mensagem" => "Nao foi possivel alterar o no.",
            "data" => [],
        ];
    }

    $stmt->close();
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
