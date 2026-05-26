<?php
include_once __DIR__ . "/valida_sessao_gerente.php";
include_once __DIR__ . "/conexao.php";

$retorno = [
    "status"   => "",
    "mensagem" => "",
    "data"     => [],
];

$id_evento_raw  = isset($_POST["id_evento"])    ? trim((string) $_POST["id_evento"])    : "";
$novo_status    = isset($_POST["novo_status"])   ? trim((string) $_POST["novo_status"])  : "";

$id_evento = ctype_digit($id_evento_raw) ? (int) $id_evento_raw : 0;

$status_permitidos = ["ativo", "recusado"];

if ($id_evento <= 0 || !in_array($novo_status, $status_permitidos, true)) {
    $retorno = [
        "status"   => "not ok",
        "mensagem" => "Dados invalidos.",
        "data"     => [],
    ];
} else {
    $id_instituicao = (int) $_SESSION["gerente_id_instituicao"];

    // Verifica se o evento pertence à instituição do gerente logado
    $stmt_check = $conexao->prepare(
        "SELECT E.id_evento
         FROM Evento E
         INNER JOIN Locais L ON L.id_local = E.id_local
         WHERE E.id_evento = ?
           AND L.id_instituicao = ?
           AND E.status = 'pendente'
         LIMIT 1"
    );
    $stmt_check->bind_param("ii", $id_evento, $id_instituicao);
    $stmt_check->execute();
    $resultado_check = $stmt_check->get_result();
    $evento_valido = $resultado_check->num_rows === 1;
    $stmt_check->close();

    if (!$evento_valido) {
        $retorno = [
            "status"   => "not ok",
            "mensagem" => "Evento nao encontrado ou sem permissao para alterar.",
            "data"     => [],
        ];
    } else {
        $stmt = $conexao->prepare("UPDATE Evento SET status = ? WHERE id_evento = ?");
        $stmt->bind_param("si", $novo_status, $id_evento);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $mensagem = $novo_status === "ativo" ? "Evento aprovado com sucesso." : "Evento recusado com sucesso.";
            $retorno = [
                "status"   => "ok",
                "mensagem" => $mensagem,
                "data"     => [],
            ];
        } else {
            $retorno = [
                "status"   => "not ok",
                "mensagem" => "Nao foi possivel alterar o status do evento.",
                "data"     => [],
            ];
        }

        $stmt->close();
    }
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
