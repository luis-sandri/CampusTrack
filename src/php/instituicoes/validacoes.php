<?php
require_once __DIR__ . "/../core/validacoes.php";

function instituicao_ler_formulario(array $origem, array &$erros): array
{
    return [
        "nome" => campo_texto_obrigatorio($origem, "nome", "Nome", $erros),
    ];
}

function instituicao_ler_id(array $origem, array &$erros): int
{
    return campo_inteiro_positivo_obrigatorio($origem, "id", "ID da instituicao", $erros);
}
