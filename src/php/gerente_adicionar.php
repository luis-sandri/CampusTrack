<?php
include_once __DIR__ . "/valida_sessao_admin.php";
include_once __DIR__ . "/conexao.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

$nome = isset($_POST["nome"]) ? trim((string) $_POST["nome"]) : "";
$email = isset($_POST["email"]) ? trim((string) $_POST["email"]) : "";
$senha = isset($_POST["senha"]) ? trim((string) $_POST["senha"]) : "";
$id_instituicao = isset($_POST["id_instituicao"]) ? (int) $_POST["id_instituicao"] : 0;
$escola = isset($_POST["escola"]) ? trim((string) $_POST["escola"]) : "";

if ($nome === "" || $email === "" || $senha === "" || $id_instituicao <= 0) {
    $retorno = [
        "status" => "not ok",
        "mensagem" => "Nome, e-mail, senha e instituição são obrigatórios.",
        "data" => [],
    ];
} else {
    // inserir na tabela Usuario
    $stmt = $conexao->prepare(
        "INSERT INTO Usuario (nome, email, senha) VALUES (?, ?, ?)"
    );
    $stmt->bind_param("sss", $nome, $email, $senha);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $id_usuario = (int) $conexao->insert_id;
        $stmt->close();

        // inserir na tabela Gerente_locais
        $stmt2 = $conexao->prepare(
            "INSERT INTO Gerente_locais (id_gerente, id_instituicao, escola) VALUES (?, ?, ?)"
        );
        $stmt2->bind_param("iis", $id_usuario, $id_instituicao, $escola);
        $stmt2->execute();

        if ($stmt2->affected_rows > 0) {
            $retorno = [
                "status" => "ok",
                "mensagem" => "Gerente cadastrado com sucesso.",
                "data" => [["id_usuario" => $id_usuario]],
            ];
        } else {
            $retorno = [
                "status" => "not ok",
                "mensagem" => "Não foi possível cadastrar o gerente.",
                "data" => [],
            ];
        }

        $stmt2->close();
    } else {
        $retorno = [
            "status" => "not ok",
            "mensagem" => "Não foi possível cadastrar o usuário. E-mail pode já estar em uso.",
            "data" => [],
        ];
        $stmt->close();
    }
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
