<?php
include_once __DIR__ . "/conexao.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

if (isset($_GET["id"])) {
    $id = (int) $_GET["id"];

    $stmt = $conexao->prepare("DELETE FROM Locais WHERE id_local = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $retorno = [
            "status" => "ok",
            "mensagem" => "Registro excluído com sucesso.",
            "data" => [],
        ];
    } else {
        $retorno = [
            "status" => "not ok",
            "mensagem" => "Não foi possível excluir o registro.",
            "data" => [],
        ];
    }

    $stmt->close();
} else {
    $retorno = [
        "status" => "not ok",
        "mensagem" => "Não foi possível excluir sem ID.",
        "data" => [],
    ];
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
