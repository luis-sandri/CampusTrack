<?php

function evento_formatar_data_exibicao(string $valor): string
{
    $data = DateTime::createFromFormat("Y-m-d H:i:s", trim($valor));
    $erros = DateTime::getLastErrors();

    if ($data === false || ($erros !== false && ($erros["warning_count"] > 0 || $erros["error_count"] > 0))) {
        return "";
    }

    return $data->format("d/m/Y H:i");
}

function evento_obter_id_instituicao_filtro(array $query, array $sessao): int
{
    $id_raw = "";

    if (isset($query["id"])) {
        $id_raw = trim((string) $query["id"]);
    } else if (isset($query["id_instituicao"])) {
        $id_raw = trim((string) $query["id_instituicao"]);
    }

    if ($id_raw !== "") {
        return ctype_digit($id_raw) ? (int) $id_raw : 0;
    }

    if (isset($sessao["aluno_id_instituicao"]) && (int) $sessao["aluno_id_instituicao"] > 0) {
        return (int) $sessao["aluno_id_instituicao"];
    }

    return 0;
}
