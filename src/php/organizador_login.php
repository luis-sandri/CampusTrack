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
        SELECT o.id_organizador, o.id_organizacao, u.id_usuario, u.nome, u.email, u.senha
        FROM Organizador o
        INNER JOIN Usuario u ON u.id_usuario = o.id_usuario
        WHERE u.email = ?
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $organizador = $resultado->fetch_assoc();

        if (password_verify($senha, $organizador["senha"])) {
            session_regenerate_id(true);

            $_SESSION["organizador_logado"] = true;
            $_SESSION["organizador_id"] = $organizador["id_organizador"];
            $_SESSION["organizador_usuario_id"] = $organizador["id_usuario"];
            $_SESSION["organizador_id_organizacao"] = $organizador["id_organizacao"];
            $_SESSION["organizador_nome"] = $organizador["nome"];
            $_SESSION["organizador_email"] = $organizador["email"];
            $_SESSION["ultima_atividade"] = time();

            unset($organizador["senha"]);
            $retorno["status"] = "ok";
            $retorno["mensagem"] = "Acesso validado com sucesso.";
            $retorno["data"] = [$organizador];
        } else {
            $retorno["status"] = "not ok";
            $retorno["mensagem"] = "E-mail ou senha invalidos.";
        }
    } else {
        $retorno["status"] = "not ok";
        $retorno["mensagem"] = "E-mail ou senha invalidos.";
    }

    $stmt->close();
}

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
