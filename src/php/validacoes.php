<?php
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
    return strlen($senha) >= 8 && preg_match("/\d/", $senha) && preg_match("/[^a-zA-Z0-9]/", $senha);
}

