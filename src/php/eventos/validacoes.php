<?php
require_once __DIR__ . "/../validacoes.php";
require_once __DIR__ . "/../evento_funcoes.php";

function evento_ler_formulario(array $origem, array &$erros): array
{
    $data_raw = campo_texto_obrigatorio($origem, "data", "Data e horario", $erros);
    $data_evento = $data_raw === "" ? "" : evento_normalizar_data_hora($data_raw);

    if ($data_raw !== "" && $data_evento === "") {
        $erros[] = "Data e horario devem estar no formato valido.";
    }

    return [
        "nome" => campo_texto_obrigatorio($origem, "nome", "Nome", $erros),
        "id_instituicao" => campo_inteiro_positivo_obrigatorio($origem, "id_instituicao", "Instituicao", $erros),
        "id_local" => campo_inteiro_positivo_obrigatorio($origem, "id_local", "Local", $erros),
        "data" => $data_evento,
    ];
}

function evento_ler_id(array $origem, array &$erros): int
{
    return campo_inteiro_positivo_obrigatorio($origem, "id_evento", "Evento", $erros);
}

function evento_ler_status(array $origem, array &$erros): string
{
    $status = campo_texto_obrigatorio($origem, "novo_status", "Status", $erros);
    $permitidos = ["ativo", "recusado"];

    if ($status !== "" && !in_array($status, $permitidos, true)) {
        $erros[] = "Status deve ser ativo ou recusado.";
    }

    return $status;
}
