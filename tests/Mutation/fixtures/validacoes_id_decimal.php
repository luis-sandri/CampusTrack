<?php

declare(strict_types=1);

function campo_valor(array $origem, string $campo): ?string
{
    if (!array_key_exists($campo, $origem)) {
        return null;
    }

    return trim((string) $origem[$campo]);
}

function campo_inteiro_positivo_obrigatorio(
    array $origem,
    string $campo,
    string $rotulo,
    array &$erros
): int {
    $valor = campo_valor($origem, $campo);

    if ($valor === null) {
        $erros[] = $rotulo . " nao foi enviado.";
        return 0;
    }

    if ($valor === "") {
        $erros[] = $rotulo . " e obrigatorio.";
        return 0;
    }

    // Mutação CT22: ctype_digit foi substituído por is_numeric.
    if (!is_numeric($valor) || (int) $valor <= 0) {
        $erros[] = $rotulo . " deve ser um numero inteiro positivo.";
        return 0;
    }

    return (int) $valor;
}

function instituicao_ler_id(array $origem, array &$erros): int
{
    return campo_inteiro_positivo_obrigatorio(
        $origem,
        "id",
        "ID da instituicao",
        $erros
    );
}