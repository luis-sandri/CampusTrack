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
        SELECT u.id_usuario, u.nome, u.email
        FROM Usuario u
        INNER JOIN Administrador a ON a.id_adm = u.id_usuario
        WHERE u.email = ? AND u.senha = ?
    ");

    $stmt->bind_param("ss", $email, $senha);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $admin = $resultado->fetch_assoc();

        // cria a sessao do administrador logado
        $_SESSION["admin_logado"] = true;
        $_SESSION["admin_id"] = $admin["id_usuario"];
        $_SESSION["admin_email"] = $admin["email"];
        $_SESSION["admin_nome"] = $admin["nome"];

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
