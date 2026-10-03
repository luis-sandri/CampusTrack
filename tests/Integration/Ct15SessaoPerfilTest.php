<?php

declare(strict_types=1);

namespace CampusTrack\Tests\Integration;

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . "/src/php/core/sessao.php";

final class Ct15SessaoPerfilTest extends TestCase
{
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testCt15ConsultaPerfilCorretoDaSessaoInicializada(): void
    {
        session_save_path(sys_get_temp_dir());
        session_id("ct15" . bin2hex(random_bytes(8)));
        \iniciar_sessao();

        $_SESSION = [
            "gerente_logado" => true,
            "gerente_id" => 101,
            "gerente_nome" => "Gerente Teste",
            "gerente_email" => "gerente@example.test",
            "gerente_id_instituicao" => 201,
            "ultima_atividade" => time(),
        ];

        $gerente = \dados_sessao_por_perfil("gerente");
        $administrador = \dados_sessao_por_perfil("admin");

        self::assertSame([
            "perfil_chave" => "gerente",
            "perfil" => "Gerente",
            "id" => 101,
            "nome" => "Gerente Teste",
            "email" => "gerente@example.test",
            "id_instituicao" => 201,
        ], $gerente);

        self::assertNull($administrador);

        $atividadeAnterior = time() - 10;
        $_SESSION["ultima_atividade"] = $atividadeAnterior;

        self::assertNotNull(\dados_sessao_por_perfil("gerente"));
        self::assertGreaterThan($atividadeAnterior, $_SESSION["ultima_atividade"]);
    }
}