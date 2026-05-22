<?php
include_once __DIR__ . "/valida_sessao_aluno.php";
include_once __DIR__ . "/conexao.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

$id_aluno = isset($_SESSION["aluno_id"]) ? (int) $_SESSION["aluno_id"] : 0;

if ($id_aluno <= 0) {
    $retorno = [
        "status" => "not ok",
        "mensagem" => "Aluno invalido.",
        "data" => [],
    ];
} else {
    $stmt = $conexao->prepare("
        SELECT F.id_favorito, F.id_local, L.nome, L.tipo_escola, L.tipo, L.capacidade, L.longitude, L.latitude,
            I.nome AS nome_instituicao
        FROM Favorito F
        INNER JOIN Locais L ON F.id_local = L.id_local
        LEFT JOIN Instituicao I ON L.id_instituicao = I.id_instituicao
        WHERE F.id_aluno = ?
        ORDER BY F.id_favorito DESC
    ");
    $stmt->bind_param("i", $id_aluno);
    $stmt->execute();
    $resultado = $stmt->get_result();

    $data = [];
    while ($row = $resultado->fetch_assoc()) {
        $data[] = $row;
    }
    $stmt->close();

    $retorno = [
        "status" => "ok",
        "mensagem" => "Lista carregada.",
        "data" => $data,
    ];
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
