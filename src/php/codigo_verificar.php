<?php
session_start();
include_once __DIR__ . "/conexao.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

$email = isset($_POST["email_verificacao"]) ? trim((string) $_POST["email_verificacao"]) : "";
$codigo_inserido = isset($_POST["codigo"]) ? trim((string) $_POST["codigo"]) : "";

if ($email === "" || $codigo_inserido === "") {
    $retorno["status"] = "not ok";
    $retorno["mensagem"] = "Dados incompletos informados.";
} else {
    // Verifica se a sessão existe e bate com os dois dados
    if (isset($_SESSION["codigo_2fa_" . $email])) {
        // Verifica se expirou
        if (time() > $_SESSION["2fa_expires_" . $email]) {
            $retorno["status"] = "not ok";
            $retorno["mensagem"] = "O código expirou. Solicite um novo código.";
            // Limpa o código antigo
            unset($_SESSION["codigo_2fa_" . $email]);
            unset($_SESSION["2fa_expires_" . $email]);
        } else {
            $codigo_salvo = (string)$_SESSION["codigo_2fa_" . $email];
            
            if ($codigo_inserido === $codigo_salvo) {
                // Sucesso
                $retorno["status"] = "ok";
                $retorno["mensagem"] = "Acesso validado com sucesso.";
                
                // Cria a sessão de estudante "logado"
                $_SESSION["aluno_logado"] = true;
                $_SESSION["aluno_email"] = $email;
                
                // Remove o código 2FA da sessão por segurança para não ser reutilizado
                unset($_SESSION["codigo_2fa_" . $email]);
                unset($_SESSION["2fa_expires_" . $email]);
            } else {
                // Código inválido
                $retorno["status"] = "not ok";
                $retorno["mensagem"] = "Código incorreto. Tente novamente.";
            }
        }
    } else {
        $retorno["status"] = "not ok";
        $retorno["mensagem"] = "Nenhum código ativo encontrado para este e-mail.";
    }
}

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
