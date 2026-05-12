<?php
include_once __DIR__ . "/valida_sessao_organizacao.php";
include_once __DIR__ . "/conexao.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

$id_organizacao = (int) $_SESSION["organizacao_id"];

if (isset($_GET["id"])) {
    $id_raw = trim((string) $_GET["id"]);
    $id = ctype_digit($id_raw) ? (int) $id_raw : 0;

    if ($id <= 0) {
        $retorno = [
            "status" => "not ok",
            "mensagem" => "ID invalido.",
            "data" => [],
        ];
    } else {
        $sql = "SELECT u.id_usuario, u.nome, u.email, o.id_organizador, o.id_organizacao
            FROM Organizador o
            INNER JOIN Usuario u ON u.id_usuario = o.id_usuario
            WHERE u.id_usuario = ? AND o.id_organizacao = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("ii", $id, $id_organizacao);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $data = [];
        while ($row = $resultado->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();

        if (count($data) > 0) {
            $retorno = [
                "status" => "ok",
                "mensagem" => "Registro encontrado.",
                "data" => $data,
            ];
        } else {
            $retorno = [
                "status" => "not ok",
                "mensagem" => "Registro nao encontrado.",
                "data" => [],
            ];
        }
    }
} else {
    $sql = "SELECT u.id_usuario, u.nome, u.email, o.id_organizador, o.id_organizacao
            FROM Organizador o
            INNER JOIN Usuario u ON u.id_usuario = o.id_usuario
            WHERE o.id_organizacao = ?
            ORDER BY o.id_organizador";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id_organizacao);
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

