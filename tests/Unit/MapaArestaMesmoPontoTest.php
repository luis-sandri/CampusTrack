<?php

declare(strict_types=1);

namespace CampusTrack\Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . "/src/php/mapa/validacoes.php";

final class MapaArestaMesmoPontoTest extends TestCase
{
    public function testCt34RejeitaConexaoDoPontoParaSiMesmo(): void
    {
        $erros = [];
        $aresta = \mapa_aresta_ler_formulario(
            [
                "id_instituicao" => "1",
                "id_no_origem" => "10",
                "id_no_destino" => "10",
            ],
            $erros
        );

        self::assertSame(
            [
                "id_instituicao" => 1,
                "id_no_origem" => 10,
                "id_no_destino" => 10,
            ],
            $aresta
        );
        self::assertSame(
            ["Origem e destino devem ser pontos diferentes."],
            $erros
        );
    }
}