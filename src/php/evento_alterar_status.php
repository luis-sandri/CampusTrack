<?php
include_once __DIR__ . "/conexao.php";
include_once __DIR__ . "/valida_sessao_organizador.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

$id_organizador = (int) $_SESSION["organizador_id"];
$id_evento = isset($_POST["id_evento"]) ? (int) $_POST["id_evento"] : 0;
$novo_status = isset($_POST["status"]) ? trim((string) $_POST["status"]) : "";

if ($id_evento <= 0 || !in_array($novo_status, ["encerrado", "cancelado"])) {
    $retorno["status"] = "not ok";
    $retorno["mensagem"] = "Parâmetros inválidos.";
} else {
    // Check if event belongs to this organizer and is 'ativo'
    $sql_check = "SELECT status FROM Evento WHERE id_evento = ? AND id_organizador = ?";
    $stmt_check = $conexao->prepare($sql_check);
    $stmt_check->bind_param("ii", $id_evento, $id_organizador);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();

    if ($res_check->num_rows === 0) {
        $retorno["status"] = "not ok";
        $retorno["mensagem"] = "Evento não encontrado ou você não tem permissão para alterá-lo.";
    } else {
        $evento = $res_check->fetch_assoc();
        
        if ($evento["status"] !== "ativo") {
            $retorno["status"] = "not ok";
            $retorno["mensagem"] = "Só é possível alterar o status de eventos ativos.";
        } else {
            $sql_update = "UPDATE Evento SET status = ? WHERE id_evento = ?";
            $stmt_update = $conexao->prepare($sql_update);
            $stmt_update->bind_param("si", $novo_status, $id_evento);
            
            if ($stmt_update->execute()) {
                $retorno["status"] = "ok";
                $retorno["mensagem"] = "Status alterado com sucesso.";
            } else {
                $retorno["status"] = "not ok";
                $retorno["mensagem"] = "Erro ao alterar o status do evento no banco de dados.";
            }
            $stmt_update->close();
        }
    }
    $stmt_check->close();
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
