<?php
require_once __DIR__ . "/../core/validacoes.php";

function autenticacao_ler_login(array $origem, array &$erros): array
{
    return [
        "email" => campo_email_obrigatorio($origem, "email", "E-mail", $erros),
        "senha" => campo_texto_obrigatorio($origem, "senha", "Senha", $erros),
    ];
}
