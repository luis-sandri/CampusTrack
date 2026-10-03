<?php

declare(strict_types=1);

namespace CampusTrack\Tests\Security;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . "/src/php/core/validacoes.php";

final class Ct24SenhaHashTest extends TestCase
{
    public function testCt24ValidaArmazenamentoDeSenhaPorHash(): void
    {
        $senhaCorreta = "Abcdef1!";
        $senhaIncorreta = "Errada1!";

        $hash = \senha_hash($senhaCorreta);

        self::assertNotSame($senhaCorreta, $hash);
        self::assertTrue(password_verify($senhaCorreta, $hash));
        self::assertFalse(password_verify($senhaIncorreta, $hash));
    }
}