<?php
session_start();
include_once __DIR__ . "/conexao.php";
include_once __DIR__ . "/validacoes.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

$email = isset($_POST["email_verificacao"]) ? trim((string) $_POST["email_verificacao"]) : "";
$codigo_inserido = isset($_POST["codigo"]) ? trim((string) $_POST["codigo"]) : "";
$nome = isset($_POST["nome"]) ? trim((string) $_POST["nome"]) : "";
$senha = isset($_POST["senha"]) ? trim((string) $_POST["senha"]) : "";
$curso = isset($_POST["curso"]) ? trim((string) $_POST["curso"]) : "";
$id_instituicao_raw = isset($_POST["id_instituicao"]) ? trim((string) $_POST["id_instituicao"]) : "";
$id_instituicao = ctype_digit($id_instituicao_raw) ? (int) $id_instituicao_raw : 0;
$modo = isset($_POST["modo"]) && $_POST["modo"] === "login" ? "login" : "cadastro";
$eh_cadastro = $modo !== "login";
$id_usuario_logado = null;
$nome_logado = $nome;
$id_instituicao_logado = $id_instituicao;

if ($email === "" || $codigo_inserido === "") {
    $retorno["status"] = "not ok";
    $retorno["mensagem"] = "Dados incompletos informados.";
} else if (!$eh_cadastro && ($senha === "" || $id_instituicao <= 0)) {
    $retorno["status"] = "not ok";
    $retorno["mensagem"] = "E-mail, senha, instituicao e codigo sao obrigatorios.";
} else if ($eh_cadastro && ($nome === "" || $senha === "" || $curso === "" || $id_instituicao <= 0)) {
    $retorno["status"] = "not ok";
    $retorno["mensagem"] = "Nome, e-mail, senha, curso, instituicao e codigo sao obrigatorios.";
} else if ($eh_cadastro && !senha_valida($senha)) {
    $retorno["status"] = "not ok";
    $retorno["mensagem"] = senha_mensagem();
} else if (!isset($_SESSION["codigo_2fa_" . $email])) {
    $retorno["status"] = "not ok";
    $retorno["mensagem"] = "Nenhum codigo ativo encontrado para este e-mail.";
} else if (time() > $_SESSION["2fa_expires_" . $email]) {
    $retorno["status"] = "not ok";
    $retorno["mensagem"] = "O codigo expirou. Solicite um novo codigo.";
    unset($_SESSION["codigo_2fa_" . $email]);
    unset($_SESSION["2fa_expires_" . $email]);
} else {
    $codigo_salvo = (string) $_SESSION["codigo_2fa_" . $email];

    if ($codigo_inserido !== $codigo_salvo) {
        $retorno["status"] = "not ok";
        $retorno["mensagem"] = "Codigo incorreto. Tente novamente.";
    } else if ($eh_cadastro) {
        if (!preg_match("/@pucpr\.edu\.br$/", $email)) {
            $retorno["status"] = "not ok";
            $retorno["mensagem"] = "O e-mail deve ser institucional (@pucpr.edu.br).";
        } else {
            $stmt = $conexao->prepare("SELECT id_usuario FROM Usuario WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $resultado = $stmt->get_result();

            if ($resultado->num_rows > 0) {
                $retorno["status"] = "not ok";
                $retorno["mensagem"] = "Este e-mail ja possui cadastro.";
                $stmt->close();
            } else {
                $stmt->close();

                $stmt = $conexao->prepare("SELECT id_instituicao FROM Instituicao WHERE id_instituicao = ?");
                $stmt->bind_param("i", $id_instituicao);
                $stmt->execute();
                $resultado = $stmt->get_result();

                if ($resultado->num_rows !== 1) {
                    $retorno["status"] = "not ok";
                    $retorno["mensagem"] = "Instituicao nao encontrada.";
                    $stmt->close();
                } else {
                    $stmt->close();
                    $conexao->begin_transaction();

                    try {
                        $senha = senha_hash($senha);
                        $stmt = $conexao->prepare("INSERT INTO Usuario (nome, email, senha) VALUES (?, ?, ?)");
                        $stmt->bind_param("sss", $nome, $email, $senha);
                        $stmt->execute();
                        $id_usuario = (int) $conexao->insert_id;
                        $stmt->close();

                        $stmt = $conexao->prepare("INSERT INTO Aluno (id_aluno, id_instituicao, curso) VALUES (?, ?, ?)");
                        $stmt->bind_param("iis", $id_usuario, $id_instituicao, $curso);
                        $stmt->execute();
                        $stmt->close();

                        $conexao->commit();

                        $retorno["status"] = "ok";
                        $retorno["mensagem"] = "Aluno cadastrado com sucesso.";
                        $retorno["data"] = [["id_usuario" => $id_usuario]];
                        $id_usuario_logado = $id_usuario;
                        $nome_logado = $nome;
                        $id_instituicao_logado = $id_instituicao;
                    } catch (Throwable $e) {
                        $conexao->rollback();
                        $retorno["status"] = "not ok";
                        $retorno["mensagem"] = "Nao foi possivel cadastrar o aluno.";
                    }
                }
            }
        }
    } else {
        $stmt = $conexao->prepare("
            SELECT u.id_usuario, u.nome, u.senha, a.id_instituicao
            FROM Usuario u
            INNER JOIN Aluno a ON a.id_aluno = u.id_usuario
            WHERE u.email = ? AND a.id_instituicao = ?
        ");
        $stmt->bind_param("si", $email, $id_instituicao);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows !== 1) {
            $retorno["status"] = "not ok";
            $retorno["mensagem"] = "E-mail ou senha invalidos.";
            $stmt->close();
        } else {
            $aluno = $resultado->fetch_assoc();
            $stmt->close();

            if (!password_verify($senha, $aluno["senha"])) {
                $retorno["status"] = "not ok";
                $retorno["mensagem"] = "E-mail ou senha invalidos.";
            } else {
                $id_usuario_logado = (int) $aluno["id_usuario"];
                $nome_logado = (string) $aluno["nome"];
                $id_instituicao_logado = (int) $aluno["id_instituicao"];

                $retorno["status"] = "ok";
                $retorno["mensagem"] = "Acesso validado com sucesso.";
                $retorno["data"] = [["id_usuario" => $id_usuario_logado]];
            }
        }
    }

    if ($retorno["status"] === "ok") {
        session_regenerate_id(true);

        $_SESSION["aluno_logado"] = true;
        $_SESSION["aluno_id"] = $id_usuario_logado;
        $_SESSION["aluno_email"] = $email;
        $_SESSION["aluno_nome"] = $nome_logado !== "" ? $nome_logado : $email;
        $_SESSION["aluno_id_instituicao"] = $id_instituicao_logado > 0 ? $id_instituicao_logado : ($_SESSION["aluno_id_instituicao"] ?? null);
        $_SESSION["ultima_atividade"] = time();

        unset($_SESSION["codigo_2fa_" . $email]);
        unset($_SESSION["2fa_expires_" . $email]);
    }
}

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
