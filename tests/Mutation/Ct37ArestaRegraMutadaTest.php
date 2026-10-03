<?php

declare(strict_types=1);

namespace CampusTrack\Tests\Mutation;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

final class Ct37ArestaRegraMutadaTest extends TestCase
{
    public function testCt37EliminaMutanteQueInverteRegraDeConexao(): void
    {
        $raizProjeto = dirname(__DIR__, 2);
        $arquivoPhpunit = $raizProjeto . "/vendor/phpunit/phpunit/phpunit";
        $testeBase = $raizProjeto . "/tests/Unit/MapaArestaValidacaoTest.php";
        $resultadoOriginal = $this->executarComando(
            [PHP_BINARY, $arquivoPhpunit, $testeBase],
            $raizProjeto
        );

        self::assertSame(0, $resultadoOriginal["codigo"]);
        self::assertStringContainsString("OK (1 test, 2 assertions)", $resultadoOriginal["saida"]);

        $raizTemporaria = sys_get_temp_dir()
            . DIRECTORY_SEPARATOR
            . "campustrack-ct37-"
            . bin2hex(random_bytes(8));

        try {
            $this->prepararCopiaIsolada($raizProjeto, $raizTemporaria);

            $arquivoValidacoes = $raizTemporaria . "/src/php/mapa/validacoes.php";
            $codigoOriginal = file_get_contents($arquivoValidacoes);
            self::assertNotFalse($codigoOriginal);

            $codigoMutante = str_replace(
                '$aresta["id_no_origem"] === $aresta["id_no_destino"]',
                '$aresta["id_no_origem"] !== $aresta["id_no_destino"]',
                $codigoOriginal,
                $substituicoes
            );

            self::assertSame(1, $substituicoes);
            self::assertNotFalse(file_put_contents($arquivoValidacoes, $codigoMutante));

            $resultadoSintaxe = $this->executarComando(
                [PHP_BINARY, "-l", $arquivoValidacoes],
                $raizProjeto
            );
            self::assertSame(0, $resultadoSintaxe["codigo"], $resultadoSintaxe["saida"]);

            $arquivoTesteMutante = $raizTemporaria . "/tests/Unit/MapaArestaValidacaoTest.php";
            $resultadoMutante = $this->executarComando(
                [PHP_BINARY, $arquivoPhpunit, $arquivoTesteMutante],
                $raizProjeto
            );

            self::assertNotSame(0, $resultadoMutante["codigo"]);
            self::assertStringContainsString("FAILURES!", $resultadoMutante["saida"]);
            self::assertStringContainsString("Failed asserting", $resultadoMutante["saida"]);
            self::assertStringNotContainsString("ERRORS!", $resultadoMutante["saida"]);
        } finally {
            $this->removerDiretorio($raizTemporaria);
        }
    }

    private function prepararCopiaIsolada(string $raizProjeto, string $raizTemporaria): void
    {
        $diretorios = [
            $raizTemporaria . "/tests/Unit",
            $raizTemporaria . "/src/php/mapa",
            $raizTemporaria . "/src/php/core",
        ];

        foreach ($diretorios as $diretorio) {
            if (!mkdir($diretorio, 0777, true) && !is_dir($diretorio)) {
                throw new RuntimeException("Nao foi possivel criar a copia temporaria do CT37.");
            }
        }

        $arquivos = [
            "/tests/Unit/MapaArestaValidacaoTest.php" => "/tests/Unit/MapaArestaValidacaoTest.php",
            "/src/php/mapa/validacoes.php" => "/src/php/mapa/validacoes.php",
            "/src/php/core/validacoes.php" => "/src/php/core/validacoes.php",
            "/src/php/core/resposta.php" => "/src/php/core/resposta.php",
        ];

        foreach ($arquivos as $origem => $destino) {
            if (!copy($raizProjeto . $origem, $raizTemporaria . $destino)) {
                throw new RuntimeException("Nao foi possivel copiar os arquivos do CT37.");
            }
        }
    }

    private function executarComando(array $comando, string $diretorio): array
    {
        $processo = proc_open(
            $comando,
            [1 => ["pipe", "w"], 2 => ["pipe", "w"]],
            $pipes,
            $diretorio
        );

        if (!is_resource($processo)) {
            throw new RuntimeException("Nao foi possivel iniciar o comando do CT37.");
        }

        $saida = stream_get_contents($pipes[1]);
        $erro = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        return [
            "codigo" => proc_close($processo),
            "saida" => $saida . $erro,
        ];
    }

    private function removerDiretorio(string $diretorio): void
    {
        if (!is_dir($diretorio)) {
            return;
        }

        $arquivos = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($diretorio, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($arquivos as $arquivo) {
            $arquivo->isDir()
                ? rmdir($arquivo->getPathname())
                : unlink($arquivo->getPathname());
        }

        rmdir($diretorio);
    }
}