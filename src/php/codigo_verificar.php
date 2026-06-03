<?php
session_start();
include_once __DIR__ . "/conexao.php";
include_once __DIR__ . "/validacoes.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

$modo = isset($_POST["modo"]) && $_POST["modo"] === "login" ? "login" : "cadastro";
$eh_cadastro = $modo !== "login";
$erros = [];

$email = campo_email_obrigatorio($_POST, "email_verificacao", "E-mail", $erros);
$codigo_inserido = campo_texto_obrigatorio($_POST, "codigo", "Codigo", $erros);
$senha = campo_texto_obrigatorio($_POST, "senha", "Senha", $erros);
$id_instituicao = campo_inteiro_positivo_obrigatorio($_POST, "id_instituicao", "Instituicao", $erros);
$nome = "";
$curso = "";
$confirmar_senha = "";

if ($codigo_inserido !== "" && !preg_match("/^\d{6}$/", $codigo_inserido)) {
    $erros[] = "Codigo deve conter 6 digitos.";
}

if ($email !== "" && !preg_match("/@pucpr\.edu\.br$/", $email)) {
    $erros[] = "O e-mail deve ser institucional (@pucpr.edu.br).";
}

if ($eh_cadastro) {
    $nome = campo_texto_obrigatorio($_POST, "nome", "Nome", $erros);
    $curso = campo_texto_obrigatorio($_POST, "curso", "Curso", $erros);
    $confirmar_senha = campo_texto_obrigatorio($_POST, "confirmar_senha", "Confirmacao de senha", $erros);

    if ($senha !== "" && !senha_valida($senha)) {
        $erros[] = senha_mensagem();
    }

    if ($senha !== "" && $confirmar_senha !== "" && $senha !== $confirmar_senha) {
        $erros[] = "As senhas nao coincidem.";
    }
}

$id_usuario_logado = null;
$nome_logado = $nome;
$id_instituicao_logado = $id_instituicao;

if (count($erros) > 0) {
    $retorno = retorno_validacao($erros);
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
                    $senha_hash = senha_hash($senha);
                    $stmt = $conexao->prepare("INSERT INTO Usuario (nome, email, senha) VALUES (?, ?, ?)");
                    $stmt->bind_param("sss", $nome, $email, $senha_hash);
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
        $_SESSION["aluno_id_instituicao"] = $id_instituicao_logado;
        $_SESSION["ultima_atividade"] = time();

        unset($_SESSION["codigo_2fa_" . $email]);
        unset($_SESSION["2fa_expires_" . $email]);
    }
}

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
