<?php

declare(strict_types=1);

namespace CampusTrack\Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . "/src/php/eventos/funcoes.php";

final class EventoNormalizarDataTest extends TestCase
{
    public function testCt25NormalizaDataValida(): void
    {
        $entrada = "2026-10-07T14:30";

        $resultado = \evento_normalizar_data_hora($entrada);

        self::assertSame(
            "2026-10-07 14:30:00",
            $resultado
        );
    }

    public function testCt26RejeitaDataImpossivel(): void
    {
        $entrada = "2026-02-30T14:30";

        $resultado = \evento_normalizar_data_hora($entrada);

        self::assertSame(
            "",
            $resultado
        );
    }
}

