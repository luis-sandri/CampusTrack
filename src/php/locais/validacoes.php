<?php
require_once __DIR__ . "/../core/validacoes.php";

function local_ler_formulario(array $origem, array &$erros): array
{
    return [
        "id_instituicao" => campo_inteiro_positivo_obrigatorio($origem, "id_instituicao", "Instituicao", $erros),
        "tipo_escola" => campo_texto_obrigatorio($origem, "tipo_escola", "Tipo de escola", $erros),
        "tipo" => campo_texto_obrigatorio($origem, "tipo", "Tipo", $erros),
        "nome" => campo_texto_obrigatorio($origem, "nome", "Nome", $erros),
        "capacidade" => campo_inteiro_positivo_obrigatorio($origem, "capacidade", "Capacidade", $erros),
        "longitude" => campo_decimal_obrigatorio($origem, "longitude", "Longitude", $erros, -180, 180),
        "latitude" => campo_decimal_obrigatorio($origem, "latitude", "Latitude", $erros, -90, 90),
    ];
}

function local_ler_id(array $origem, array &$erros): int
{
    return campo_inteiro_positivo_obrigatorio($origem, "id", "ID do local", $erros);
}
