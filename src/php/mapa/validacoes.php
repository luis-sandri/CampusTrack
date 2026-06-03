<?php
require_once __DIR__ . "/../validacoes.php";

function mapa_no_ler_formulario(array $origem, array &$erros): array
{
    return [
        "id_instituicao" => campo_inteiro_positivo_obrigatorio($origem, "id_instituicao", "Instituicao", $erros),
        "nome" => campo_texto_obrigatorio($origem, "nome", "Nome", $erros),
        "longitude" => campo_decimal_obrigatorio($origem, "longitude", "Longitude", $erros, -180, 180),
        "latitude" => campo_decimal_obrigatorio($origem, "latitude", "Latitude", $erros, -90, 90),
    ];
}

function mapa_no_ler_id(array $origem, array &$erros): int
{
    return campo_inteiro_positivo_obrigatorio($origem, "id", "ID do no", $erros);
}

function mapa_aresta_ler_formulario(array $origem, array &$erros): array
{
    $aresta = [
        "id_instituicao" => campo_inteiro_positivo_obrigatorio($origem, "id_instituicao", "Instituicao", $erros),
        "id_no_origem" => campo_inteiro_positivo_obrigatorio($origem, "id_no_origem", "Origem", $erros),
        "id_no_destino" => campo_inteiro_positivo_obrigatorio($origem, "id_no_destino", "Destino", $erros),
    ];

    if ($aresta["id_no_origem"] > 0 && $aresta["id_no_origem"] === $aresta["id_no_destino"]) {
        $erros[] = "Origem e destino devem ser pontos diferentes.";
    }

    return $aresta;
}

function mapa_aresta_ler_id(array $origem, array &$erros): int
{
    return campo_inteiro_positivo_obrigatorio($origem, "id", "ID da conexao", $erros);
}

function mapa_ler_id_instituicao(array $origem, array &$erros): int
{
    return campo_inteiro_positivo_obrigatorio($origem, "id_instituicao", "Instituicao", $erros);
}
