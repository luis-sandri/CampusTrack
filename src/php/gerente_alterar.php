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
    $email = isset($_POST["email"]) ? trim((string) $_POST["email"]) : "";
    $senha = isset($_POST["senha"]) ? trim((string) $_POST["senha"]) : "";
    $id_instituicao = isset($_POST["id_instituicao"]) ? (int) $_POST["id_instituicao"] : 0;
    $escola = isset($_POST["escola"]) ? trim((string) $_POST["escola"]) : "";

    if ($id <= 0 || $nome === "" || $email === "" || $senha === "" || $id_instituicao <= 0) {
        $retorno = [
            "status" => "not ok",
            "mensagem" => "Dados inválidos.",
            "data" => [],
        ];
    } else {
        // atualizar tabela Usuario
        $stmt = $conexao->prepare(
            "UPDATE Usuario SET nome = ?, email = ?, senha = ? WHERE id_usuario = ?"
        );
        $stmt->bind_param("sssi", $nome, $email, $senha, $id);
        $stmt->execute();
        $stmt->close();

        // atualizar tabela Gerente_locais
        $stmt2 = $conexao->prepare(
            "UPDATE Gerente_locais SET id_instituicao = ?, escola = ? WHERE id_gerente = ?"
        );
        $stmt2->bind_param("isi", $id_instituicao, $escola, $id);
        $stmt2->execute();

        if ($stmt2->affected_rows >= 0) {
            $retorno = [
                "status" => "ok",
                "mensagem" => "Gerente alterado com sucesso.",
                "data" => [],
            ];
        } else {
            $retorno = [
                "status" => "not ok",
                "mensagem" => "Não foi possível alterar o gerente.",
                "data" => [],
            ];
        }

        $stmt2->close();
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
