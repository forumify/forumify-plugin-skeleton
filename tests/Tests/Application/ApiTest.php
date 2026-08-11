<?php

declare(strict_types=1);

namespace PluginTests\Tests\Application;

use Forumify\Testing\Api\ApiTestCase;
use Forumify\Testing\Api\Crud\DeleteTestTrait;
use Forumify\Testing\Api\Crud\GetCollectionTestTrait;
use Forumify\Testing\Api\Crud\GetTestTrait;
use Forumify\Testing\Api\Crud\PatchTestTrait;
use Forumify\Testing\Api\Crud\PostTestTrait;
use PluginTests\Tests\Factories\ExampleFactory;

/**
 * The CRUD traits cover the standard operations, so all you have to declare is the
 * factory, the endpoint and what a valid body looks like.
 */
class ApiTest extends ApiTestCase
{
    use GetTestTrait;
    use GetCollectionTestTrait;
    use PostTestTrait;
    use PatchTestTrait;
    use DeleteTestTrait;

    protected static function factory(): string
    {
        return ExampleFactory::class;
    }

    protected static function endpoint(): string
    {
        return '/api/examples';
    }

    protected function getPostBody(): array
    {
        return ['title' => 'Created through the API'];
    }

    protected function getPatchBody(): array
    {
        return ['title' => 'Updated through the API'];
    }
}
