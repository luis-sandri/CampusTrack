<?php
require_once __DIR__ . "/../validacoes.php";

function organizador_ler_cadastro(array $origem, array &$erros): array
{
    $organizador = organizador_ler_dados_base($origem, $erros);
    $organizador["senha"] = campo_texto_obrigatorio($origem, "senha", "Senha", $erros);

    if ($organizador["senha"] !== "" && !senha_valida($organizador["senha"])) {
        $erros[] = senha_mensagem();
    }

    return $organizador;
}

function organizador_ler_alteracao(array $origem, array &$erros): array
{
    return organizador_ler_dados_base($origem, $erros);
}

function organizador_ler_id(array $origem, array &$erros): int
{
    return campo_inteiro_positivo_obrigatorio($origem, "id", "ID do organizador", $erros);
}

function organizador_ler_dados_base(array $origem, array &$erros): array
{
    return [
        "nome" => campo_texto_obrigatorio($origem, "nome", "Nome", $erros),
        "email" => campo_email_obrigatorio($origem, "email", "E-mail", $erros),
    ];
}
