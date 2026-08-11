<?php

declare(strict_types=1);

namespace PluginTests\Tests\Unit;

use Forumify\PluginSkeleton\Entity\Example;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests need no database and no kernel, so keep anything that doesn't touch
 * forumify itself in here: it runs in milliseconds.
 */
class EntityTest extends TestCase
{
    public function testTitleRoundTrips(): void
    {
        $example = new Example();
        $example->setTitle('Hello');

        self::assertSame('Hello', $example->getTitle());
        self::assertNull($example->getContent());
    }
}
