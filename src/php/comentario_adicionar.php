<?php
include_once __DIR__ . "/valida_sessao_aluno.php";
include_once __DIR__ . "/conexao.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

$id_evento = isset($_POST["id_evento"]) ? trim((string) $_POST["id_evento"]) : "";
$comentario = isset($_POST["comentario"]) ? trim((string) $_POST["comentario"]) : "";
$id_aluno = (int) $_SESSION["aluno_id"];

if (!ctype_digit($id_evento) || $id_evento <= 0 || empty($comentario)) {
    $retorno = [
        "status" => "not ok",
        "mensagem" => "Dados invalidos. Verifique os campos enviados.",
        "data" => [],
    ];
} else if (strlen($comentario) > 140) {
    $retorno = [
        "status" => "not ok",
        "mensagem" => "O comentario não pode ultrapassar 140 caracteres.",
        "data" => [],
    ];
} else {
    $id_evento = (int) $id_evento;

    // Verificar se o evento existe, está ativo e já foi encerrado (> data + 2 horas)
    $stmt_verifica = $conexao->prepare("SELECT data, status FROM Evento WHERE id_evento = ? AND status IN ('ativo', 'encerrado')");
    $stmt_verifica->bind_param("i", $id_evento);
    $stmt_verifica->execute();
    $resultado_verifica = $stmt_verifica->get_result();
    
    if ($resultado_verifica->num_rows === 0) {
        $retorno = [
            "status" => "not ok",
            "mensagem" => "Evento não encontrado ou não está ativo.",
            "data" => [],
        ];
    } else {
        $evento = $resultado_verifica->fetch_assoc();
        $data_timestamp = strtotime($evento["data"]);
        $data_fim_timestamp = $data_timestamp + (120 * 60); // 2 horas após
        $data_atual_timestamp = time();

        if ($evento["status"] !== "encerrado" && $data_atual_timestamp <= $data_fim_timestamp) {
            $retorno = [
                "status" => "not ok",
                "mensagem" => "O evento ainda não foi encerrado.",
                "data" => [],
            ];
        } else {
            // Verificar se o aluno já comentou (opcional, mas bom pra não floodar)
            $stmt_ja_comentou = $conexao->prepare("SELECT id_comentario FROM Comentario WHERE id_aluno = ? AND id_evento = ?");
            $stmt_ja_comentou->bind_param("ii", $id_aluno, $id_evento);
            $stmt_ja_comentou->execute();
            $resultado_ja_comentou = $stmt_ja_comentou->get_result();

            if ($resultado_ja_comentou->num_rows > 0) {
                $retorno = [
                    "status" => "not ok",
                    "mensagem" => "Você já enviou um feedback para este evento.",
                    "data" => [],
                ];
            } else {
                $stmt = $conexao->prepare("INSERT INTO Comentario (id_aluno, id_evento, comentario) VALUES (?, ?, ?)");
                $stmt->bind_param("iis", $id_aluno, $id_evento, $comentario);
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                    $retorno = [
                        "status" => "ok",
                        "mensagem" => "Avaliação enviada com sucesso",
                        "data" => [],
                    ];
                } else {
                    $retorno = [
                        "status" => "not ok",
                        "mensagem" => "Não foi possível enviar a avaliação.",
                        "data" => [],
                    ];
                }
                $stmt->close();
            }
            $stmt_ja_comentou->close();
        }
    }
    $stmt_verifica->close();
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
