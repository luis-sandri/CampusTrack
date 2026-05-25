<?php
include_once __DIR__ . "/conexao.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

$id_instituicao_raw = isset($_GET["id_instituicao"]) ? trim((string) $_GET["id_instituicao"]) : "";
$id_instituicao = ctype_digit($id_instituicao_raw) ? (int) $id_instituicao_raw : 0;

if ($id_instituicao <= 0) {
    $retorno = [
        "status" => "not ok",
        "mensagem" => "Instituicao invalida.",
        "data" => [],
    ];
} else {
    $nos = [];
    $arestas = [];

    $stmt = $conexao->prepare("
        SELECT id_no, id_instituicao, nome, longitude, latitude
        FROM Mapa_No
        WHERE id_instituicao = ?
        ORDER BY id_no
    ");
    $stmt->bind_param("i", $id_instituicao);
    $stmt->execute();
    $resultado = $stmt->get_result();

    while ($row = $resultado->fetch_assoc()) {
        $nos[] = $row;
    }
    $stmt->close();

    $stmt = $conexao->prepare("
        SELECT id_aresta, id_instituicao, id_no_origem, id_no_destino, distancia_metros
        FROM Mapa_Aresta
        WHERE id_instituicao = ?
        ORDER BY id_aresta
    ");
    $stmt->bind_param("i", $id_instituicao);
    $stmt->execute();
    $resultado = $stmt->get_result();

    while ($row = $resultado->fetch_assoc()) {
        $arestas[] = $row;
    }
    $stmt->close();

    $retorno = [
        "status" => "ok",
        "mensagem" => "Grafo carregado.",
        "data" => [[
            "nos" => $nos,
            "arestas" => $arestas,
        ]],
    ];
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
