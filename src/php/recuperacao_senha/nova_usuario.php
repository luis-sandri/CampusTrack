<?php
session_start();
include_once __DIR__ . "/../core/conexao.php";
require_once __DIR__ . "/../core/validacoes.php";
require_once __DIR__ . "/repositorio.php";

$erros = [];
$email = campo_email_obrigatorio($_POST, "email", "E-mail", $erros);
$codigo = campo_texto_obrigatorio($_POST, "codigo", "Codigo", $erros);
$senha_nova = campo_texto_obrigatorio($_POST, "senha_nova", "Nova senha", $erros);

if ($codigo !== "" && !preg_match("/^\d{6}$/", $codigo)) {
    $erros[] = "Codigo deve conter 6 digitos.";
}

if ($senha_nova !== "" && !senha_valida($senha_nova)) {
    $erros[] = senha_mensagem();
}

if (count($erros) > 0) {
    responder_json(resposta_validacao($erros));
}

$chave_codigo = "codigo_recuperacao_" . $email;
$chave_expira = "recuperacao_expires_" . $email;
$chave_usuario = "recuperacao_id_usuario_" . $email;

if (!isset($_SESSION[$chave_codigo], $_SESSION[$chave_expira], $_SESSION[$chave_usuario])) {
    responder_json(resposta_erro("Nenhum codigo ativo encontrado para este e-mail. Solicite novamente."));
}

if (time() > (int) $_SESSION[$chave_expira]) {
    recuperacao_limpar_sessao_usuario($email);
    responder_json(resposta_erro("O codigo expirou. Solicite um novo codigo."));
}

if ($codigo !== (string) $_SESSION[$chave_codigo]) {
    responder_json(resposta_erro("Codigo incorreto. Tente novamente."));
}

$id_usuario = (int) $_SESSION[$chave_usuario];
$senha_hash = senha_hash($senha_nova);

if (!recuperacao_atualizar_senha_usuario($conexao, $id_usuario, $senha_hash)) {
    responder_json(resposta_erro("Nao foi possivel atualizar a senha."));
}

recuperacao_limpar_sessao_usuario($email);
responder_json(resposta_ok("Senha redefinida com sucesso."));

function recuperacao_limpar_sessao_usuario(string $email): void
{
    unset($_SESSION["codigo_recuperacao_" . $email]);
    unset($_SESSION["recuperacao_expires_" . $email]);
    unset($_SESSION["recuperacao_id_usuario_" . $email]);
}
