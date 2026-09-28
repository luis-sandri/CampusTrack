<?php

declare(strict_types=1);

namespace CampusTrack\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class PhpUnitSetupTest extends TestCase
{
    public function testAmbientePossuiVersaoEExtensoesNecessarias(): void
    {
        self::assertTrue(version_compare(PHP_VERSION, "8.2.0", ">="));
        self::assertTrue(extension_loaded("mysqli"));
        self::assertTrue(extension_loaded("ctype"));
    }
}

