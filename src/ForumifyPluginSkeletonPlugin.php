<?php

declare(strict_types=1);

namespace Forumify\PluginSkeleton;

use Forumify\Plugin\AbstractForumifyPlugin;
use Forumify\Plugin\PluginMetadata;

class ForumifyPluginSkeletonPlugin extends AbstractForumifyPlugin
{
    public function getPluginMetadata(): PluginMetadata
    {
        return new PluginMetadata(
            'Plugin Skeleton',
            'Skeleton Author',
            'A starting point for building forumify plugins.',
            'https://plugin.example.com',
        );
    }

    // skeleton:if admin
    /**
     * Permissions your plugin adds.
     *
     * They are checked as "<slugged-plugin-name>.admin.example.view", where the prefix
     * is the plugin name from the metadata above.
     */
    public function getPermissions(): array
    {
        return [
            'admin' => [
                'example' => [
                    'view',
                    'manage',
                ],
            ],
        ];
    }
    // skeleton:endif
}
