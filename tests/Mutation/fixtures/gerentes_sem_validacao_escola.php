<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . "/src/php/core/validacoes.php";

function gerente_ler_cadastro(array $origem, array &$erros): array
{
    $gerente = gerente_ler_dados_base($origem, $erros);
    $gerente["senha"] = campo_texto_obrigatorio($origem, "senha", "Senha", $erros);

    if ($gerente["senha"] !== "" && !senha_valida($gerente["senha"])) {
        $erros[] = senha_mensagem();
    }

    return $gerente;
}

function gerente_ler_dados_base(array $origem, array &$erros): array
{
    return [
        "nome" => campo_texto_obrigatorio($origem, "nome", "Nome", $erros),
        "email" => campo_email_obrigatorio($origem, "email", "E-mail", $erros),
        "id_instituicao" => campo_inteiro_positivo_obrigatorio($origem, "id_instituicao", "Instituicao", $erros),

        // Mutação CT21: a validação obrigatória de escola foi removida.
        "escola" => campo_valor($origem, "escola") ?? "",
    ];
}