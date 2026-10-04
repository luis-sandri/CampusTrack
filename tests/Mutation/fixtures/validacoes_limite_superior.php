<?php

declare(strict_types=1);

function campo_valor(array $origem, string $campo): ?string
{
    if (!array_key_exists($campo, $origem)) {
        return null;
    }

    return trim((string) $origem[$campo]);
}

function campo_decimal_obrigatorio(
    array $origem,
    string $campo,
    string $rotulo,
    array &$erros,
    $minimo = null,
    $maximo = null,
    $maximo_casas_decimais = null
): string {
    $valor = campo_valor($origem, $campo);

    if ($valor === null) {
        $erros[] = $rotulo . " nao foi enviado.";
        return "";
    }

    if ($valor === "") {
        $erros[] = $rotulo . " e obrigatorio.";
        return "";
    }

    $normalizado = str_replace(",", ".", $valor);

    if (!preg_match("/^[+-]?\d+(\.\d+)?$/", $normalizado)) {
        $erros[] = $rotulo . " deve ser um numero valido.";
        return "";
    }

    if ($maximo_casas_decimais !== null) {
        $partes = explode(".", $normalizado, 2);
        $casas_decimais = isset($partes[1]) ? strlen($partes[1]) : 0;

        if ($casas_decimais > $maximo_casas_decimais) {
            $erros[] = $rotulo . " deve ter no maximo " . $maximo_casas_decimais . " casas decimais.";
            return "";
        }
    }

    $numero = (float) $normalizado;

    if ($minimo !== null && $numero < $minimo) {
        $erros[] = $rotulo . " deve ser maior ou igual a " . $minimo . ".";
    }

    // Mutação CT06
    // Original: $numero > $maximo
    if ($maximo !== null && $numero >= $maximo) {
        $erros[] = $rotulo . " deve ser menor ou igual a " . $maximo . ".";
    }

    return $normalizado;
}