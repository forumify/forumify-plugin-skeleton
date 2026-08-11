<?php

declare(strict_types=1);

namespace Forumify\PluginSkeleton\Admin\Service;

use Forumify\Admin\Service\SettingsMenuProviderInterface;
use Forumify\Core\MenuBuilder\Menu;
use Forumify\Core\MenuBuilder\MenuItem;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Adds your plugin to the admin settings menu by decorating forumify's own provider.
 */
#[AsDecorator(SettingsMenuProviderInterface::class)]
class SettingsMenuProvider implements SettingsMenuProviderInterface
{
    public function __construct(
        #[AutowireDecorated]
        private readonly SettingsMenuProviderInterface $decorated,
    ) {
    }

    public function provide(UrlGeneratorInterface $u, TranslatorInterface $t): Menu
    {
        $menu = $this->decorated->provide($u, $t);
        $menu->addItem(new MenuItem(
            $t->trans('admin.plugin_skeleton.title'),
            $u->generate('forumify_admin_plugin_skeleton_index'),
            [
                // Any icon from https://phosphoricons.com
                'icon' => 'ph ph-puzzle-piece',
                'permission' => 'plugin-skeleton.admin.example.view',
            ],
        ));

        return $menu;
    }
}
