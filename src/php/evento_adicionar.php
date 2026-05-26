<?php
include_once __DIR__ . "/valida_sessao_organizador.php";
include_once __DIR__ . "/conexao.php";
include_once __DIR__ . "/evento_funcoes.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

$dados = [
    "nome" => isset($_POST["nome"]) ? trim((string) $_POST["nome"]) : "",
    "id_instituicao" => isset($_POST["id_instituicao"]) ? trim((string) $_POST["id_instituicao"]) : "",
    "id_local" => isset($_POST["id_local"]) ? trim((string) $_POST["id_local"]) : "",
    "data" => isset($_POST["data"]) ? trim((string) $_POST["data"]) : "",
];

if (!evento_campos_obrigatorios_preenchidos($dados)) {
    $retorno = [
        "status" => "not ok",
        "mensagem" => "preencha toddos os campos",
        "data" => [],
    ];
} else {
    $id_instituicao = ctype_digit($dados["id_instituicao"]) ? (int) $dados["id_instituicao"] : 0;
    $id_local = ctype_digit($dados["id_local"]) ? (int) $dados["id_local"] : 0;
    $data_evento = evento_normalizar_data_hora($dados["data"]);
    $id_organizador = (int) $_SESSION["organizador_id"];
    $id_organizacao = (int) $_SESSION["organizador_id_organizacao"];

    if ($id_instituicao <= 0 || $id_local <= 0 || $data_evento === "") {
        $retorno = [
            "status" => "not ok",
            "mensagem" => "preencha toddos os campos",
            "data" => [],
        ];
    } else {
        $data_timestamp = strtotime($data_evento);
        $data_atual_timestamp = time();

        if ($data_timestamp <= $data_atual_timestamp) {
            $retorno = [
                "status" => "not ok",
                "mensagem" => "A data e horário do evento devem ser no futuro.",
                "data" => [],
            ];
        } else {
            $stmt_local = $conexao->prepare("SELECT id_local FROM Locais WHERE id_local = ? AND id_instituicao = ?");
            $stmt_local->bind_param("ii", $id_local, $id_instituicao);
            $stmt_local->execute();
            $resultado_local = $stmt_local->get_result();
            $local_existe = $resultado_local->num_rows === 1;
            $stmt_local->close();

        if (!$local_existe) {
            $retorno = [
                "status" => "not ok",
                "mensagem" => "Local invalido para a instituicao selecionada.",
                "data" => [],
            ];
        } else {
            $stmt_conflito = $conexao->prepare(
                "SELECT id_evento FROM Evento WHERE id_local = ? AND status = 'ativo' AND ABS(TIMESTAMPDIFF(MINUTE, data, ?)) < 120 LIMIT 1"
            );
            $stmt_conflito->bind_param("is", $id_local, $data_evento);
            $stmt_conflito->execute();
            $resultado_conflito = $stmt_conflito->get_result();
            $tem_conflito = $resultado_conflito->num_rows > 0;
            $stmt_conflito->close();

            if ($tem_conflito) {
                $retorno = [
                    "status" => "not ok",
                    "mensagem" => "Local indisponível para o período selecionado",
                    "data" => [],
                ];
            } else {
                $stmt = $conexao->prepare(
                    "INSERT INTO Evento (nome, data, status, id_local, id_organizacao, id_organizador)
                     VALUES (?, ?, 'pendente', ?, ?, ?)"
                );
                $stmt->bind_param("ssiii", $dados["nome"], $data_evento, $id_local, $id_organizacao, $id_organizador);
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                    $retorno = [
                        "status" => "ok",
                        "mensagem" => "evento solicitado com sucesso",
                        "data" => [["id_evento" => (int) $conexao->insert_id]],
                    ];
                } else {
                    $retorno = [
                        "status" => "not ok",
                        "mensagem" => "Nao foi possivel cadastrar o evento.",
                        "data" => [],
                    ];
                }

                $stmt->close();
            }
        }
        }
    }
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
