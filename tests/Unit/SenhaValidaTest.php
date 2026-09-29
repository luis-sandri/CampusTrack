<?php

declare(strict_types=1);

namespace CampusTrack\Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . "/src/php/core/validacoes.php";

final class SenhaValidaTest extends TestCase
{
    public function testCt09AceitaSenhaComOitoCaracteresMaiusculaDigitoESimbolo(): void
    {
        $senha = "Abcde1!x";

        $resultado = \senha_valida($senha);

        self::assertTrue($resultado);
    }

    public function testCt10RejeitaSenhaSemLetraMaiuscula(): void
    {
        $senha = "abcde1!x";

        $resultado = \senha_valida($senha);

        self::assertFalse($resultado);
    }
}
