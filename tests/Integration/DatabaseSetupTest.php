<?php

declare(strict_types=1);

namespace CampusTrack\Tests\Integration;

use CampusTrack\Tests\Support\DatabaseTestCase;

final class DatabaseSetupTest extends DatabaseTestCase
{
    public function testConexaoUsaBancoExclusivoDeTeste(): void
    {
        $resultado = $this->conexao->query("SELECT DATABASE() AS banco_atual");

        self::assertSame(
            "campustrack_test",
            $resultado->fetch_assoc()["banco_atual"] ?? null
        );
    }

    public function testEsquemaESeedBaseEstaoDisponiveis(): void
    {
        $tabelasEsperadas = [
            "Administrador",
            "Aluno",
            "Comentario",
            "Evento",
            "Favorito",
            "Gerente_Locais",
            "Instituicao",
            "Locais",
            "Mapa_Aresta",
            "Mapa_No",
            "Organizacao",
            "Organizador",
            "Usuario",
        ];

        $resultado = $this->conexao->query("SHOW TABLES");
        $tabelasEncontradas = [];

        while ($linha = $resultado->fetch_row()) {
            $tabelasEncontradas[] = strtolower($linha[0]);
        }

        $tabelasEsperadas = array_map("strtolower", $tabelasEsperadas);

        foreach ($tabelasEsperadas as $tabelaEsperada) {
            self::assertContains($tabelaEsperada, $tabelasEncontradas);
        }

        self::assertGreaterThanOrEqual(3, $this->contarRegistros("Instituicao"));
        self::assertGreaterThanOrEqual(2, $this->contarRegistros("Aluno"));
        self::assertGreaterThanOrEqual(6, $this->contarRegistros("Locais"));
        self::assertGreaterThanOrEqual(2, $this->contarRegistros("Evento"));
    }

    private function contarRegistros(string $tabela): int
    {
        $resultado = $this->conexao->query("SELECT COUNT(*) AS total FROM " . $tabela);

        return (int) ($resultado->fetch_assoc()["total"] ?? 0);
    }
}

