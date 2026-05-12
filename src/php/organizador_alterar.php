<?php
include_once __DIR__ . "/valida_sessao_organizacao.php";
include_once __DIR__ . "/conexao.php";
include_once __DIR__ . "/validacoes.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

$id_organizacao = (int) $_SESSION["organizacao_id"];

if (isset($_GET["id"])) {
    $id_raw = trim((string) $_GET["id"]);
    $id = ctype_digit($id_raw) ? (int) $id_raw : 0;
    $nome = isset($_POST["nome"]) ? trim((string) $_POST["nome"]) : "";
    $email = isset($_POST["email"]) ? trim((string) $_POST["email"]) : "";
    $senha = isset($_POST["senha"]) ? trim((string) $_POST["senha"]) : "";

    if ($id <= 0 || $nome === "" || $email === "" || $senha === "") {
        $retorno = [
            "status" => "not ok",
            "mensagem" => "Dados invalidos.",
            "data" => [],
        ];
    } else if (!senha_valida($senha)) {
        $retorno = [
            "status" => "not ok",
            "mensagem" => senha_mensagem(),
            "data" => [],
        ];
    } else {
        $stmt = $conexao->prepare("SELECT id_organizador FROM Organizador WHERE id_usuario = ? AND id_organizacao = ?");
        $stmt->bind_param("ii", $id, $id_organizacao);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows !== 1) {
            $retorno = [
                "status" => "not ok",
                "mensagem" => "Organizador nao encontrado.",
                "data" => [],
            ];
            $stmt->close();
        } else {
            $stmt->close();

            $stmt = $conexao->prepare("UPDATE Usuario SET nome = ?, email = ?, senha = ? WHERE id_usuario = ?");
            $senha = senha_hash($senha);
            $stmt->bind_param("sssi", $nome, $email, $senha, $id);
            $stmt->execute();
            $stmt->close();

            $retorno = [
                "status" => "ok",
                "mensagem" => "Organizador alterado com sucesso.",
                "data" => [],
            ];
        }
    }
} else {
    $retorno = [
        "status" => "not ok",
        "mensagem" => "Nao foi possivel alterar o registro sem ID.",
        "data" => [],
    ];
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
