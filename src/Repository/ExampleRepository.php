<?php

declare(strict_types=1);

namespace Forumify\PluginSkeleton\Repository;

use Forumify\Core\Repository\AbstractRepository;
use Forumify\PluginSkeleton\Entity\Example;

/**
 * @extends AbstractRepository<Example>
 */
class ExampleRepository extends AbstractRepository
{
    public static function getEntityClass(): string
    {
        return Example::class;
    }
}
