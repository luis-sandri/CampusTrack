<?php
session_start();
include_once __DIR__ . "/../core/conexao.php";
require_once __DIR__ . "/../core/validacoes.php";
require_once __DIR__ . "/repositorio.php";

$erros = [];
$cnpj = preg_replace("/\D/", "", campo_texto_obrigatorio($_POST, "cnpj", "CNPJ", $erros));
$codigo = campo_texto_obrigatorio($_POST, "codigo", "Codigo", $erros);
$senha_nova = campo_texto_obrigatorio($_POST, "senha_nova", "Nova senha", $erros);

if ($cnpj !== "" && !cnpj_valido($cnpj)) {
    $erros[] = "CNPJ invalido.";
}

if ($codigo !== "" && !preg_match("/^\d{6}$/", $codigo)) {
    $erros[] = "Codigo deve conter 6 digitos.";
}

if ($senha_nova !== "" && !senha_valida($senha_nova)) {
    $erros[] = senha_mensagem();
}

if (count($erros) > 0) {
    responder_json(resposta_validacao($erros));
}

$chave_codigo = "codigo_recuperacao_org_" . $cnpj;
$chave_expira = "recuperacao_expires_org_" . $cnpj;
$chave_organizacao = "recuperacao_id_org_" . $cnpj;

if (!isset($_SESSION[$chave_codigo], $_SESSION[$chave_expira], $_SESSION[$chave_organizacao])) {
    responder_json(resposta_erro("Nenhum codigo ativo encontrado. Solicite novamente."));
}

if (time() > (int) $_SESSION[$chave_expira]) {
    recuperacao_limpar_sessao_organizacao($cnpj);
    responder_json(resposta_erro("O codigo expirou. Solicite um novo codigo."));
}

if ($codigo !== (string) $_SESSION[$chave_codigo]) {
    responder_json(resposta_erro("Codigo incorreto. Tente novamente."));
}

$id_organizacao = (int) $_SESSION[$chave_organizacao];
$senha_hash = senha_hash($senha_nova);

if (!recuperacao_atualizar_senha_organizacao($conexao, $id_organizacao, $senha_hash)) {
    responder_json(resposta_erro("Nao foi possivel atualizar a senha."));
}

recuperacao_limpar_sessao_organizacao($cnpj);
responder_json(resposta_ok("Senha redefinida com sucesso."));

function recuperacao_limpar_sessao_organizacao(string $cnpj): void
{
    unset($_SESSION["codigo_recuperacao_org_" . $cnpj]);
    unset($_SESSION["recuperacao_expires_org_" . $cnpj]);
    unset($_SESSION["recuperacao_id_org_" . $cnpj]);
}
