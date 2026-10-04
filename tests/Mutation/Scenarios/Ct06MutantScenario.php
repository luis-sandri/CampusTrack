<?php

declare(strict_types=1);

namespace CampusTrack\Tests\Mutation\Scenarios;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . "/fixtures/validacoes_limite_superior.php";

final class Ct06MutantScenario extends TestCase
{
    public function testCT04AceitarLatitudeNoLimite90(): void
    {
        $dados = [
            "latitude" => "90",
        ];

        $erros = [];

        $resultado = \campo_decimal_obrigatorio(
            $dados,
            "latitude",
            "Latitude",
            $erros,
            -90,
            90,
            15
        );

        self::assertEmpty($erros);
        self::assertSame("90", $resultado);
    }
}