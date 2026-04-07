<?php
session_start();
include_once __DIR__ . "/conexao.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

if (!isset($_SESSION["gerente_logado"]) || $_SESSION["gerente_logado"] !== true) {
    $retorno = [
        "status" => "not ok",
        "mensagem" => "Acesso negado.",
        "data" => [],
    ];
} else {
    $id_instituicao = (int) $_SESSION["gerente_id_instituicao"];

    $stmt = $conexao->prepare("SELECT id_instituicao, nome FROM Instituicao WHERE id_instituicao = ?");
    $stmt->bind_param("i", $id_instituicao);
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
            "mensagem" => "Lista carregada.",
            "data" => $data,
        ];
    } else {
        $retorno = [
            "status" => "not ok",
            "mensagem" => "Instituição não encontrada.",
            "data" => [],
        ];
    }
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
