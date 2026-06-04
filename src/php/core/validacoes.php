<?php
require_once __DIR__ . "/resposta.php";

function cpf_valido(string $cpf): bool
{
    $cpf = preg_replace("/\D/", "", $cpf);

    if (strlen($cpf) !== 11 || preg_match("/^(\d)\1{10}$/", $cpf)) {
        return false;
    }

    for ($t = 9; $t < 11; $t++) {
        $soma = 0;
        for ($i = 0; $i < $t; $i++) {
            $soma += (int) $cpf[$i] * (($t + 1) - $i);
        }

        $digito = ((10 * $soma) % 11) % 10;
        if ((int) $cpf[$t] !== $digito) {
            return false;
        }
    }

    return true;
}

function cnpj_valido(string $cnpj): bool
{
    $cnpj = preg_replace("/\D/", "", $cnpj);

    if (strlen($cnpj) !== 14 || preg_match("/^(\d)\1{13}$/", $cnpj)) {
        return false;
    }

    $pesos1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    $pesos2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

    $soma = 0;
    for ($i = 0; $i < 12; $i++) {
        $soma += (int) $cnpj[$i] * $pesos1[$i];
    }

    $digito = $soma % 11 < 2 ? 0 : 11 - ($soma % 11);
    if ((int) $cnpj[12] !== $digito) {
        return false;
    }

    $soma = 0;
    for ($i = 0; $i < 13; $i++) {
        $soma += (int) $cnpj[$i] * $pesos2[$i];
    }

    $digito = $soma % 11 < 2 ? 0 : 11 - ($soma % 11);
    return (int) $cnpj[13] === $digito;
}

function senha_valida(string $senha): bool
{
    return strlen($senha) >= 8 && preg_match("/[A-Z]/", $senha) && preg_match("/\d/", $senha) && preg_match("/[^a-zA-Z0-9]/", $senha);
}

function senha_mensagem(): string
{
    return "A senha deve ter pelo menos 8 caracteres, 1 letra maiuscula, 1 numero e 1 simbolo.";
}

function senha_hash(string $senha): string
{
    return password_hash($senha, PASSWORD_BCRYPT);
}

function campo_valor(array $origem, string $campo): ?string
{
    if (!array_key_exists($campo, $origem)) {
        return null;
    }

    return trim((string) $origem[$campo]);
}

function campo_texto_obrigatorio(array $origem, string $campo, string $rotulo, array &$erros): string
{
    $valor = campo_valor($origem, $campo);

    if ($valor === null) {
        $erros[] = $rotulo . " nao foi enviado.";
        return "";
    }

    if ($valor === "") {
        $erros[] = $rotulo . " e obrigatorio.";
        return "";
    }

    return $valor;
}

function campo_email_obrigatorio(array $origem, string $campo, string $rotulo, array &$erros): string
{
    $valor = campo_texto_obrigatorio($origem, $campo, $rotulo, $erros);

    if ($valor !== "" && !filter_var($valor, FILTER_VALIDATE_EMAIL)) {
        $erros[] = $rotulo . " invalido.";
    }

    return $valor;
}

function campo_inteiro_positivo_obrigatorio(array $origem, string $campo, string $rotulo, array &$erros): int
{
    $valor = campo_valor($origem, $campo);

    if ($valor === null) {
        $erros[] = $rotulo . " nao foi enviado.";
        return 0;
    }

    if ($valor === "") {
        $erros[] = $rotulo . " e obrigatorio.";
        return 0;
    }

    if (!ctype_digit($valor) || (int) $valor <= 0) {
        $erros[] = $rotulo . " deve ser um numero inteiro positivo.";
        return 0;
    }

    return (int) $valor;
}

function campo_decimal_obrigatorio(array $origem, string $campo, string $rotulo, array &$erros, $minimo = null, $maximo = null): string
{
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

    if (!is_numeric($normalizado)) {
        $erros[] = $rotulo . " deve ser um numero valido.";
        return "";
    }

    $numero = (float) $normalizado;

    if ($minimo !== null && $numero < $minimo) {
        $erros[] = $rotulo . " deve ser maior ou igual a " . $minimo . ".";
    }

    if ($maximo !== null && $numero > $maximo) {
        $erros[] = $rotulo . " deve ser menor ou igual a " . $maximo . ".";
    }

    return $normalizado;
}

function retorno_validacao(array $erros): array
{
    return resposta_validacao($erros);
}

