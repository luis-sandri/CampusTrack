<?php
const TEMPO_INATIVIDADE = 1800;

function iniciar_sessao(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function encerrar_sessao(): void
{
    iniciar_sessao();
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            "",
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();
}

function marcar_sessao_ativa(): void
{
    iniciar_sessao();
    $_SESSION["ultima_atividade"] = time();
}

function sessao_em_tempo_valido(): bool
{
    iniciar_sessao();

    if (!isset($_SESSION["ultima_atividade"])) {
        $_SESSION["ultima_atividade"] = time();
        return true;
    }

    if ((time() - (int) $_SESSION["ultima_atividade"]) > TEMPO_INATIVIDADE) {
        encerrar_sessao();
        return false;
    }

    $_SESSION["ultima_atividade"] = time();
    return true;
}

function dados_sessao_por_perfil(string $perfil): ?array
{
    iniciar_sessao();

    if (!sessao_em_tempo_valido()) {
        return null;
    }

    if ($perfil === "admin" && isset($_SESSION["admin_logado"]) && $_SESSION["admin_logado"] === true) {
        return [
            "perfil_chave" => "admin",
            "perfil" => "Administrador",
            "id" => $_SESSION["admin_id"] ?? null,
            "nome" => $_SESSION["admin_nome"] ?? "Administrador",
            "email" => $_SESSION["admin_email"] ?? "",
        ];
    }

    if ($perfil === "gerente" && isset($_SESSION["gerente_logado"]) && $_SESSION["gerente_logado"] === true) {
        return [
            "perfil_chave" => "gerente",
            "perfil" => "Gerente",
            "id" => $_SESSION["gerente_id"] ?? null,
            "nome" => $_SESSION["gerente_nome"] ?? "Gerente",
            "email" => $_SESSION["gerente_email"] ?? "",
            "id_instituicao" => $_SESSION["gerente_id_instituicao"] ?? null,
        ];
    }

    if ($perfil === "organizacao" && isset($_SESSION["organizacao_logada"]) && $_SESSION["organizacao_logada"] === true) {
        return [
            "perfil_chave" => "organizacao",
            "perfil" => "Organizacao",
            "id" => $_SESSION["organizacao_id"] ?? null,
            "nome" => $_SESSION["organizacao_nome"] ?? "Organizacao",
            "email" => "",
        ];
    }

    if ($perfil === "organizador" && isset($_SESSION["organizador_logado"]) && $_SESSION["organizador_logado"] === true) {
        return [
            "perfil_chave" => "organizador",
            "perfil" => "Organizador",
            "id" => $_SESSION["organizador_id"] ?? null,
            "nome" => $_SESSION["organizador_nome"] ?? "Organizador",
            "email" => $_SESSION["organizador_email"] ?? "",
        ];
    }

    if ($perfil === "aluno" && isset($_SESSION["aluno_logado"]) && $_SESSION["aluno_logado"] === true) {
        return [
            "perfil_chave" => "aluno",
            "perfil" => "Estudante",
            "id" => $_SESSION["aluno_id"] ?? null,
            "nome" => $_SESSION["aluno_nome"] ?? ($_SESSION["aluno_email"] ?? "Estudante"),
            "email" => $_SESSION["aluno_email"] ?? "",
            "id_instituicao" => $_SESSION["aluno_id_instituicao"] ?? null,
        ];
    }

    return null;
}

function exigir_sessao(string $perfil): void
{
    $dados = dados_sessao_por_perfil($perfil);

    if ($dados === null) {
        header("Content-type:application/json;charset=utf-8");
        echo json_encode([
            "status" => "not ok",
            "mensagem" => "Sessao expirada ou acesso negado.",
            "data" => [],
        ]);
        exit;
    }
}
