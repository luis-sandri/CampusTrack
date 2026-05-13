<?php
session_start();
include_once __DIR__ . "/conexao.php";
include_once __DIR__ . "/validacoes.php";

$retorno = [
    "status" => "",
    "mensagem" => ""
];

$email = isset($_POST["email"]) ? trim((string) $_POST["email"]) : "";
$codigo_inserido = isset($_POST["codigo"]) ? trim((string) $_POST["codigo"]) : "";
$senha_nova = isset($_POST["senha_nova"]) ? trim((string) $_POST["senha_nova"]) : "";

if ($email === "" || $codigo_inserido === "" || $senha_nova === "") {
    $retorno["status"] = "not ok";
    $retorno["mensagem"] = "E-mail, código e nova senha são obrigatórios.";
} else if (!senha_valida($senha_nova)) {
    $retorno["status"] = "not ok";
    $retorno["mensagem"] = senha_mensagem();
} else if (!isset($_SESSION["codigo_recuperacao_" . $email])) {
    $retorno["status"] = "not ok";
    $retorno["mensagem"] = "Nenhum código ativo encontrado para este e-mail. Solicite novamente.";
} else if (time() > $_SESSION["recuperacao_expires_" . $email]) {
    $retorno["status"] = "not ok";
    $retorno["mensagem"] = "O código expirou. Solicite um novo código.";
    unset($_SESSION["codigo_recuperacao_" . $email]);
    unset($_SESSION["recuperacao_expires_" . $email]);
    unset($_SESSION["recuperacao_id_usuario_" . $email]);
} else {
    $codigo_salvo = (string) $_SESSION["codigo_recuperacao_" . $email];
    $id_usuario = (int) $_SESSION["recuperacao_id_usuario_" . $email];

    if ($codigo_inserido !== $codigo_salvo) {
        $retorno["status"] = "not ok";
        $retorno["mensagem"] = "Código incorreto. Tente novamente.";
    } else {
        // Update password
        $senha_hash = senha_hash($senha_nova);
        $stmt = $conexao->prepare("UPDATE Usuario SET senha = ? WHERE id_usuario = ?");
        $stmt->bind_param("si", $senha_hash, $id_usuario);
        
        if ($stmt->execute()) {
            $retorno["status"] = "ok";
            $retorno["mensagem"] = "Senha redefinida com sucesso.";
            
            // Clear session variables
            unset($_SESSION["codigo_recuperacao_" . $email]);
            unset($_SESSION["recuperacao_expires_" . $email]);
            unset($_SESSION["recuperacao_id_usuario_" . $email]);
        } else {
            $retorno["status"] = "not ok";
            $retorno["mensagem"] = "Erro ao atualizar a senha no banco de dados.";
        }
        $stmt->close();
    }
}

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
