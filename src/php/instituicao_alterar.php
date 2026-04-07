<?php
include_once __DIR__ . "/valida_sessao_admin.php";
include_once __DIR__ . "/conexao.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

if (isset($_GET["id"])) {
    $id = (int) $_GET["id"];
    $nome = isset($_POST["nome"]) ? trim((string) $_POST["nome"]) : "";

    if ($id <= 0 || $nome === "") {
        $retorno = [
            "status" => "not ok",
            "mensagem" => "Dados inválidos.",
            "data" => [],
        ];
    } else {
        $stmt = $conexao->prepare(
            "UPDATE Instituicao SET nome = ? WHERE id_instituicao = ?"
        );
        $stmt->bind_param("si", $nome, $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $retorno = [
                "status" => "ok",
                "mensagem" => "Instituição alterada com sucesso.",
                "data" => [],
            ];
        } else {
            $retorno = [
                "status" => "not ok",
                "mensagem" => "Não foi possível alterar a instituição.",
                "data" => [],
            ];
        }

        $stmt->close();
    }
} else {
    $retorno = [
        "status" => "not ok",
        "mensagem" => "Não foi possível alterar o registro sem ID.",
        "data" => [],
    ];
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
