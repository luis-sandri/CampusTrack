<?php

declare(strict_types=1);

namespace CampusTrack\Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . "/src/php/eventos/validacoes.php";

final class EventoLerStatusTest extends TestCase
{
    public function testCt27AceitaStatusAtivo(): void
    {
        $origem = [
            "novo_status" => "ativo"
        ];
        $erros = [];

        $resultado = \evento_ler_status($origem, $erros);

        self::assertSame("ativo", $resultado);
        self::assertEmpty($erros);
    }

    public function testCt28RejeitaStatusNaoPermitido(): void
    {
        $origem = [
            "novo_status" => "qualquer"
        ];
        $erros = [];

        $resultado = \evento_ler_status($origem, $erros);

        self::assertSame("qualquer", $resultado);
        self::assertContains(
            "Status deve ser ativo ou recusado.",
            $erros
        );
    }
}


