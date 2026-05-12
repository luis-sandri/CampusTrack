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
$nome = isset($_POST["nome"]) ? trim((string) $_POST["nome"]) : "";
$email = isset($_POST["email"]) ? trim((string) $_POST["email"]) : "";
$senha = isset($_POST["senha"]) ? trim((string) $_POST["senha"]) : "";

if ($nome === "" || $email === "" || $senha === "") {
    $retorno["status"] = "not ok";
    $retorno["mensagem"] = "Nome, e-mail e senha sao obrigatorios.";
} else if (!senha_valida($senha)) {
    $retorno["status"] = "not ok";
    $retorno["mensagem"] = senha_mensagem();
} else {
    $stmt = $conexao->prepare("SELECT id_usuario FROM Usuario WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $retorno["status"] = "not ok";
        $retorno["mensagem"] = "Ja existe um usuario cadastrado com este e-mail.";
        $stmt->close();
    } else {
        $stmt->close();
        $conexao->begin_transaction();

        try {
            $senha = senha_hash($senha);
            $stmt = $conexao->prepare("INSERT INTO Usuario (nome, email, senha) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nome, $email, $senha);
            $stmt->execute();
            $id_usuario = (int) $conexao->insert_id;
            $stmt->close();

            $stmt = $conexao->prepare("INSERT INTO Organizador (id_usuario, id_organizacao) VALUES (?, ?)");
            $stmt->bind_param("ii", $id_usuario, $id_organizacao);
            $stmt->execute();
            $id_organizador = (int) $conexao->insert_id;
            $stmt->close();

            $conexao->commit();

            $retorno["status"] = "ok";
            $retorno["mensagem"] = "Organizador cadastrado com sucesso.";
            $retorno["data"] = [["id_organizador" => $id_organizador]];
        } catch (Throwable $e) {
            $conexao->rollback();
            $retorno["status"] = "not ok";
            $retorno["mensagem"] = "Nao foi possivel cadastrar o organizador.";
        }
    }
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
