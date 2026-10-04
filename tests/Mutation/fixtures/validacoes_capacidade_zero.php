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

    // Mutação do CT05
    // Original: (int) $valor <= 0
    if (!ctype_digit($valor) || (int) $valor < 0) {
        $erros[] = $rotulo . " deve ser um numero inteiro positivo.";
        return 0;
    }

    return (int) $valor;
}