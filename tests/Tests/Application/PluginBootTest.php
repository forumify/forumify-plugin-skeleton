<?php

declare(strict_types=1);

namespace PluginTests\Tests\Application;

use Forumify\PluginSkeleton\ForumifyPluginSkeletonPlugin;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Proves the plugin is installed, registered and boots inside a real forumify
 * application. If this fails, something is wrong with the plugin's wiring rather than
 * with your own code.
 */
class PluginBootTest extends KernelTestCase
{
    public function testPluginIsRegistered(): void
    {
        self::bootKernel();

        $bundles = self::$kernel?->getBundles() ?? [];

        self::assertArrayHasKey('ForumifyPluginSkeletonPlugin', $bundles);
        self::assertInstanceOf(ForumifyPluginSkeletonPlugin::class, $bundles['ForumifyPluginSkeletonPlugin']);
    }
}
