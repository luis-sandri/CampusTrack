<?php

function resposta_ok(string $mensagem, array $data = []): array
{
    return [
        "status" => "ok",
        "mensagem" => $mensagem,
        "data" => $data,
    ];
}

function resposta_erro(string $mensagem, array $erros = []): array
{
    $resposta = [
        "status" => "not ok",
        "mensagem" => $mensagem,
        "data" => [],
    ];

    if (count($erros) > 0) {
        $resposta["erros"] = $erros;
    }

    return $resposta;
}

function resposta_validacao(array $erros): array
{
    return resposta_erro(implode(" ", $erros), $erros);
}

function responder_json(array $resposta): void
{
    header("Content-type:application/json;charset=utf-8");
    echo json_encode($resposta);
    exit;
}
