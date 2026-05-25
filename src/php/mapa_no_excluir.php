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
$id_instituicao_raw = isset($_GET["id_instituicao"]) ? trim((string) $_GET["id_instituicao"]) : "";
$id_instituicao = ctype_digit($id_instituicao_raw) ? (int) $id_instituicao_raw : 0;

if ($id <= 0 || $id_instituicao <= 0) {
    $retorno = [
        "status" => "not ok",
        "mensagem" => "Dados invalidos.",
        "data" => [],
    ];
} else {
    $stmt = $conexao->prepare("DELETE FROM Mapa_No WHERE id_no = ? AND id_instituicao = ?");
    $stmt->bind_param("ii", $id, $id_instituicao);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $retorno = [
            "status" => "ok",
            "mensagem" => "No excluido com sucesso.",
            "data" => [],
        ];
    } else {
        $retorno = [
            "status" => "not ok",
            "mensagem" => "Nao foi possivel excluir o no.",
            "data" => [],
        ];
    }

    $stmt->close();
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
