<?php
include_once __DIR__ . "/valida_sessao_admin.php";
include_once __DIR__ . "/conexao.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

$id_instituicao_raw = isset($_POST["id_instituicao"]) ? trim((string) $_POST["id_instituicao"]) : "";
$id_instituicao = ctype_digit($id_instituicao_raw) ? (int) $id_instituicao_raw : 0;
$nome = isset($_POST["nome"]) ? trim((string) $_POST["nome"]) : "";
$longitude = isset($_POST["longitude"]) ? trim((string) $_POST["longitude"]) : "";
$latitude = isset($_POST["latitude"]) ? trim((string) $_POST["latitude"]) : "";

if ($id_instituicao <= 0 || $nome === "" || $longitude === "" || $latitude === "" || !is_numeric($longitude) || !is_numeric($latitude)) {
    $retorno = [
        "status" => "not ok",
        "mensagem" => "Instituicao, nome, longitude e latitude validos sao obrigatorios.",
        "data" => [],
    ];
} else {
    $stmt = $conexao->prepare("SELECT id_instituicao FROM Instituicao WHERE id_instituicao = ?");
    $stmt->bind_param("i", $id_instituicao);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows !== 1) {
        $retorno = [
            "status" => "not ok",
            "mensagem" => "Instituicao nao encontrada.",
            "data" => [],
        ];
        $stmt->close();
    } else {
        $stmt->close();
        $stmt = $conexao->prepare("INSERT INTO Mapa_No (id_instituicao, nome, longitude, latitude) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $id_instituicao, $nome, $longitude, $latitude);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $retorno = [
                "status" => "ok",
                "mensagem" => "No cadastrado com sucesso.",
                "data" => [["id_no" => (int) $conexao->insert_id]],
            ];
        } else {
            $retorno = [
                "status" => "not ok",
                "mensagem" => "Nao foi possivel cadastrar o no.",
                "data" => [],
            ];
        }

        $stmt->close();
    }
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
