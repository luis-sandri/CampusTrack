<?php
include_once __DIR__ . "/conexao.php";
include_once __DIR__ . "/valida_sessao_aluno.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

$id_aluno = (int) $_SESSION["aluno_id"];
$id_evento = isset($_POST["id_evento"]) ? (int) $_POST["id_evento"] : 0;
$comentario = isset($_POST["comentario"]) ? trim((string) $_POST["comentario"]) : "";

if ($id_evento <= 0 || $comentario === "") {
    $retorno["status"] = "not ok";
    $retorno["mensagem"] = "preencha os campos obrigatórios";
} else {
    // Check if event exists and is 'encerrado'
    $sql_check = "SELECT status FROM Evento WHERE id_evento = ?";
    $stmt_check = $conexao->prepare($sql_check);
    $stmt_check->bind_param("i", $id_evento);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();

    if ($res_check->num_rows === 0) {
        $retorno["status"] = "not ok";
        $retorno["mensagem"] = "Evento não encontrado.";
    } else {
        $evento = $res_check->fetch_assoc();
        if ($evento["status"] !== "encerrado") {
            $retorno["status"] = "not ok";
            $retorno["mensagem"] = "O evento não está encerrado.";
        } else {
            // Check if already commented
            $sql_exist = "SELECT id_comentario FROM Comentario WHERE id_aluno = ? AND id_evento = ?";
            $stmt_exist = $conexao->prepare($sql_exist);
            $stmt_exist->bind_param("ii", $id_aluno, $id_evento);
            $stmt_exist->execute();
            $res_exist = $stmt_exist->get_result();
            if ($res_exist->num_rows > 0) {
                $retorno["status"] = "not ok";
                $retorno["mensagem"] = "Você já avaliou este evento.";
            } else {
                $sql_insert = "INSERT INTO Comentario (id_aluno, id_evento, comentario) VALUES (?, ?, ?)";
                $stmt_insert = $conexao->prepare($sql_insert);
                $stmt_insert->bind_param("iis", $id_aluno, $id_evento, $comentario);
                
                if ($stmt_insert->execute()) {
                    $retorno["status"] = "ok";
                    $retorno["mensagem"] = "Feedback enviado com sucesso!";
                } else {
                    $retorno["status"] = "not ok";
                    $retorno["mensagem"] = "Erro ao salvar avaliação.";
                }
                $stmt_insert->close();
            }
            $stmt_exist->close();
        }
    }
    $stmt_check->close();
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
