<?php

declare(strict_types=1);

namespace PluginTests\Tests\Application;

// skeleton:if entities
use PluginTests\Tests\Factories\ExampleFactory;
// skeleton:endif
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;

/**
 * Application tests boot a real forumify kernel and make real requests, so they need a
 * database. Run `make tests` and the database is set up for you.
 *
 * Every test runs inside a transaction that is rolled back afterwards, so tests never
 * see each other's data and there is nothing to clean up.
 */
class FrontendControllerTest extends WebTestCase
{
    use Factories;

    public function testIndexPageLoads(): void
    {
        $client = static::createClient();

        // skeleton:if entities
        ExampleFactory::createOne(['title' => 'First example']);
        // skeleton:endif

        $client->request('GET', '/plugin-skeleton');

        self::assertResponseIsSuccessful();
        // skeleton:if entities
        self::assertSelectorTextContains('body', 'First example');
        // skeleton:endif
    }
}
