<?php

declare(strict_types=1);

function senha_valida(string $senha): bool
{
    return strlen($senha) > 8
        && preg_match("/[A-Z]/", $senha)
        && preg_match("/\d/", $senha)
        && preg_match("/[^a-zA-Z0-9]/", $senha);
}
