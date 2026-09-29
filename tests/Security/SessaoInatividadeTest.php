<?php

declare(strict_types=1);

namespace CampusTrack\Tests\Security;

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . "/src/php/core/sessao.php";

final class SessaoInatividadeTest extends TestCase
{
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testCt16RevogaSessaoAposInatividade(): void
    {
        session_save_path(sys_get_temp_dir());
        session_id("ct16" . bin2hex(random_bytes(8)));
        \iniciar_sessao();

        $_SESSION = [
            "gerente_logado" => true,
            "gerente_id" => 101,
            "gerente_nome" => "Gerente Teste",
            "gerente_email" => "gerente@example.test",
            "gerente_id_instituicao" => 201,
            "ultima_atividade" => time() - (\TEMPO_INATIVIDADE + 1),
        ];

        $dados = \dados_sessao_por_perfil("gerente");

        self::assertNull($dados);
        self::assertSame([], $_SESSION);
        self::assertSame(PHP_SESSION_NONE, session_status());
    }
}
