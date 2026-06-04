<?php
require_once __DIR__ . "/../core/validacoes.php";

function organizacao_ler_cadastro(array $origem, array &$erros): array
{
    $organizacao = [
        "nome" => campo_texto_obrigatorio($origem, "nome", "Nome", $erros),
        "cnpj" => organizacao_ler_cnpj($origem, $erros),
        "senha" => campo_texto_obrigatorio($origem, "senha", "Senha", $erros),
    ];
    $confirmar_senha = campo_texto_obrigatorio($origem, "confirmar_senha", "Confirmacao de senha", $erros);

    if ($organizacao["senha"] !== "" && !senha_valida($organizacao["senha"])) {
        $erros[] = senha_mensagem();
    }

    if ($organizacao["senha"] !== "" && $confirmar_senha !== "" && $organizacao["senha"] !== $confirmar_senha) {
        $erros[] = "As senhas nao coincidem.";
    }

    return $organizacao;
}

function organizacao_ler_login(array $origem, array &$erros): array
{
    return [
        "cnpj" => organizacao_ler_cnpj($origem, $erros),
        "senha" => campo_texto_obrigatorio($origem, "senha", "Senha", $erros),
    ];
}

function organizacao_ler_cnpj(array $origem, array &$erros): string
{
    $cnpj_raw = campo_texto_obrigatorio($origem, "cnpj", "CNPJ", $erros);
    $cnpj = preg_replace("/\D/", "", $cnpj_raw);

    if ($cnpj !== "" && !cnpj_valido($cnpj)) {
        $erros[] = "CNPJ invalido.";
    }

    return $cnpj;
}
