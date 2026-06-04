<?php
require_once __DIR__ . "/../core/validacoes.php";
require_once __DIR__ . "/../eventos/funcoes.php";

function disponibilidade_ler_data(array $origem, array &$erros): string
{
    $data_raw = campo_texto_obrigatorio($origem, "data", "Data e horario", $erros);
    $data = $data_raw === "" ? "" : evento_normalizar_data_hora($data_raw);

    if ($data_raw !== "" && $data === "") {
        $erros[] = "Data ou horario invalidos. Informe um periodo futuro para realizar a consulta.";
    }

    if ($data !== "" && strtotime($data) <= time()) {
        $erros[] = "Data ou horario invalidos. Informe um periodo futuro para realizar a consulta.";
    }

    return $data;
}
