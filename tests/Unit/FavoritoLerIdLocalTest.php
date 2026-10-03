<?php

declare(strict_types=1);

namespace CampusTrack\Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . "/src/php/favoritos/validacoes.php";

final class FavoritoLerIdLocalTest extends TestCase
{
    public function testCt35AceitaIdDeLocalFavoritoValido(): void
    {
        $erros = [];
        $idLocal = \favorito_ler_id_local(["id_local" => "10"], $erros);

        self::assertSame(10, $idLocal);
        self::assertSame([], $erros);
    }
}