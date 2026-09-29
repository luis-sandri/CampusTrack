<?php

declare(strict_types=1);

namespace CampusTrack\Tests\Mutation;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . "/src/php/core/validacoes.php";

final class SenhaMinimoMutanteTest extends TestCase
{
    public function testCt13DetectaAumentoIndevidoDoMinimoDaSenha(): void
    {
        $senha = "Abcde1!x";

        $resultadoOriginal = \senha_valida($senha);
        $resultadoMutante = $this->senhaValidaComMinimoMaiorQueOito($senha);

        self::assertTrue($resultadoOriginal);
        self::assertFalse($resultadoMutante);
    }

    private function senhaValidaComMinimoMaiorQueOito(string $senha): bool
    {
        return strlen($senha) > 8
            && preg_match("/[A-Z]/", $senha)
            && preg_match("/\d/", $senha)
            && preg_match("/[^a-zA-Z0-9]/", $senha);
    }
}
