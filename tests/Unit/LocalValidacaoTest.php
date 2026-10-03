<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../src/php/locais/validacoes.php';

class LocalValidacaoTest extends TestCase
{
    public function testCT02RejeitarNomeSomenteComEspacos(): void
    {
        $dados = [
            "id_instituicao" => "1",
            "tipo_escola" => "Tecnologia",
            "tipo" => "Laboratório",
            "nome" => "   ",
            "capacidade" => "30",
            "longitude" => "-49.25",
            "latitude" => "-25.45",
        ];

        $erros = [];

        $resultado = local_ler_formulario($dados, $erros);

        $this->assertSame("", $resultado["nome"]);
        $this->assertCount(1, $erros);
        $this->assertContains("Nome e obrigatorio.", $erros);
    }
}