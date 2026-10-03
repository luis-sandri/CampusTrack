<?php

declare(strict_types=1);

namespace CampusTrack\Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . "/src/php/favoritos/validacoes.php";

final class FavoritoIdLocalAusenteTest extends TestCase
{
    public function testCt36RejeitaIdDeLocalFavoritoAusente(): void
    {
        $erros = [];
        $idLocal = \favorito_ler_id_local([], $erros);

        self::assertSame(0, $idLocal);
        self::assertSame(["Local nao foi enviado."], $erros);
    }
}