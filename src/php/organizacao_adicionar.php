<?php
include_once __DIR__ . "/conexao.php";
include_once __DIR__ . "/validacoes.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

$nome = isset($_POST["nome"]) ? trim((string) $_POST["nome"]) : "";
$cnpj = isset($_POST["cnpj"]) ? preg_replace("/\D/", "", (string) $_POST["cnpj"]) : "";
$senha = isset($_POST["senha"]) ? trim((string) $_POST["senha"]) : "";

if ($nome === "" || $cnpj === "" || $senha === "") {
    $retorno["status"] = "not ok";
    $retorno["mensagem"] = "Nome, CNPJ e senha sao obrigatorios.";
} else if (!cnpj_valido($cnpj)) {
    $retorno["status"] = "not ok";
    $retorno["mensagem"] = "CNPJ invalido.";
} else if (!senha_valida($senha)) {
    $retorno["status"] = "not ok";
    $retorno["mensagem"] = senha_mensagem();
} else {
    $stmt = $conexao->prepare("SELECT id_organizacao FROM Organizacao WHERE cnpj = ?");
    $stmt->bind_param("s", $cnpj);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $retorno["status"] = "not ok";
        $retorno["mensagem"] = "Ja existe uma organizacao cadastrada com este CNPJ.";
        $stmt->close();
    } else {
        $stmt->close();

        $stmt = $conexao->prepare("INSERT INTO Organizacao (nome, cnpj, senha) VALUES (?, ?, ?)");
        $senha = senha_hash($senha);
        $stmt->bind_param("sss", $nome, $cnpj, $senha);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $retorno["status"] = "ok";
            $retorno["mensagem"] = "Organizacao cadastrada com sucesso.";
            $retorno["data"] = [["id_organizacao" => (int) $conexao->insert_id]];
        } else {
            $retorno["status"] = "not ok";
            $retorno["mensagem"] = "Nao foi possivel cadastrar a organizacao.";
        }

        $stmt->close();
    }
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
