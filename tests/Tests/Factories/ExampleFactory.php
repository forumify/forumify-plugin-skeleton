<?php

declare(strict_types=1);

namespace PluginTests\Tests\Factories;

use Forumify\PluginSkeleton\Entity\Example;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * Factories build entities for your tests. forumify ships factories for its own
 * entities (users, roles, forums, ...) under Forumify\Testing\Factories.
 *
 * @extends PersistentObjectFactory<Example>
 */
class ExampleFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Example::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'title' => self::faker()->unique()->sentence(3),
            'content' => self::faker()->paragraph(),
        ];
    }
}
