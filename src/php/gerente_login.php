<?php
session_start();
include_once __DIR__ . "/conexao.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

$email = isset($_POST["email"]) ? trim((string) $_POST["email"]) : "";
$senha = isset($_POST["senha"]) ? trim((string) $_POST["senha"]) : "";

if ($email === "" || $senha === "") {
    $retorno["status"] = "not ok";
    $retorno["mensagem"] = "Preencha todos os campos.";
} else {
    $stmt = $conexao->prepare("
        SELECT u.id_usuario, u.nome, u.email, g.id_instituicao
        FROM Usuario u
        INNER JOIN Gerente_locais g ON g.id_gerente = u.id_usuario
        WHERE u.email = ? AND u.senha = ?
    ");

    $stmt->bind_param("ss", $email, $senha);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $gerente = $resultado->fetch_assoc();

        // cria a sessao do gerente logado
        $_SESSION["gerente_logado"] = true;
        $_SESSION["gerente_id"] = $gerente["id_usuario"];
        $_SESSION["gerente_email"] = $gerente["email"];
        $_SESSION["gerente_nome"] = $gerente["nome"];
        $_SESSION["gerente_id_instituicao"] = $gerente["id_instituicao"];

        $retorno["status"] = "ok";
        $retorno["mensagem"] = "Acesso validado com sucesso.";
    } else {
        $retorno["status"] = "not ok";
        $retorno["mensagem"] = "E-mail ou senha inválidos.";
    }

    $stmt->close();
}

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);