<?php

function evento_campos_obrigatorios_preenchidos(array $dados): bool
{
    $campos = ["nome", "id_instituicao", "id_local", "data"];

    foreach ($campos as $campo) {
        if (!isset($dados[$campo]) || trim((string) $dados[$campo]) === "") {
            return false;
        }
    }

    return true;
}

function evento_normalizar_data_hora(string $valor): string
{
    $valor = trim($valor);

    if ($valor === "") {
        return "";
    }

    $data = DateTime::createFromFormat("Y-m-d\TH:i", $valor);
    $erros = DateTime::getLastErrors();

    if ($data === false || ($erros !== false && ($erros["warning_count"] > 0 || $erros["error_count"] > 0))) {
        return "";
    }

    return $data->format("Y-m-d H:i:s");
}
