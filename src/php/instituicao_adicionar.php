<?php
include_once __DIR__ . "/valida_sessao_admin.php";
include_once __DIR__ . "/conexao.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

$nome = isset($_POST["nome"]) ? trim((string) $_POST["nome"]) : "";

if ($nome === "") {
    $retorno = [
        "status" => "not ok",
        "mensagem" => "Nome é obrigatório.",
        "data" => [],
    ];
} else {
    $stmt = $conexao->prepare(
        "INSERT INTO Instituicao (nome) VALUES (?)"
    );
    $stmt->bind_param("s", $nome);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $retorno = [
            "status" => "ok",
            "mensagem" => "Instituição cadastrada com sucesso.",
            "data" => [["id_instituicao" => (int) $conexao->insert_id]],
        ];
    } else {
        $retorno = [
            "status" => "not ok",
            "mensagem" => "Não foi possível cadastrar a instituição.",
            "data" => [],
        ];
    }

    $stmt->close();
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
