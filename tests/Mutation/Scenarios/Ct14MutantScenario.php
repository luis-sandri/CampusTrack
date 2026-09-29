<?php

declare(strict_types=1);

namespace CampusTrack\Tests\Mutation\Scenarios;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . "/fixtures/validacoes_sem_maiuscula.php";

final class Ct14MutantScenario extends TestCase
{
    public function testCt10RejeitaSenhaSemLetraMaiuscula(): void
    {
        $senha = "abcde1!x";

        $resultado = \senha_valida($senha);

        self::assertFalse($resultado);
    }
}
