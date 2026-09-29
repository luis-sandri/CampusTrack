<?php

declare(strict_types=1);

namespace CampusTrack\Tests\Mutation\Scenarios;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . "/fixtures/validacoes_minimo_maior_que_oito.php";

final class Ct13MutantScenario extends TestCase
{
    public function testCt09AceitaSenhaComOitoCaracteresMaiusculaDigitoESimbolo(): void
    {
        $senha = "Abcde1!x";

        $resultado = \senha_valida($senha);

        self::assertTrue($resultado);
    }
}
