<?php
require_once __DIR__ . "/../core/validacoes.php";

function favorito_ler_id_local(array $origem, array &$erros): int
{
    return campo_inteiro_positivo_obrigatorio($origem, "id_local", "Local", $erros);
}
