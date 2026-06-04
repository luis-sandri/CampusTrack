<?php
require_once __DIR__ . "/../core/validacoes.php";

function comentario_ler_formulario(array $origem, array &$erros): array
{
    $comentario = campo_texto_obrigatorio($origem, "comentario", "Comentario", $erros);

    if ($comentario !== "" && strlen($comentario) > 140) {
        $erros[] = "O comentario nao pode ultrapassar 140 caracteres.";
    }

    return [
        "id_evento" => campo_inteiro_positivo_obrigatorio($origem, "id_evento", "Evento", $erros),
        "comentario" => $comentario,
    ];
}
