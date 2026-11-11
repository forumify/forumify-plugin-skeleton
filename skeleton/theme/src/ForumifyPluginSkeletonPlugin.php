<?php

declare(strict_types=1);

namespace Forumify\PluginSkeleton;

use Forumify\ForumifyTheme;
use Forumify\Plugin\PluginMetadata;

/**
 * The entry point forumify uses to recognise this package as a theme.
 *
 * It extends forumify's base theme instead of starting from nothing. Only the active
 * theme's variables are written to the page, and forumify's own styles rely on all of
 * them (--c-primary, --c-elevation-0, --border-radius, ...). Inheriting them keeps the
 * site intact while you restyle it piece by piece.
 *
 * To give admins more to configure under Admin → Settings → Themes, add your own
 * variables on top of the inherited ones:
 *
 *     public function getThemeConfig(): ThemeConfig
 *     {
 *         $base = parent::getThemeConfig();
 *
 *         return new ThemeConfig($base->hasDarkVariant, [
 *             ...$base->vars,
 *             new ThemeVar('c-header', 'Header Color', ThemeVarType::Color, '#2d3142', '#d5d8e3'),
 *         ]);
 *     }
 *
 * Every variable is available to your stylesheets as a CSS custom property, the one
 * above as var(--c-header).
 */
class ForumifyPluginSkeletonPlugin extends ForumifyTheme
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

    /**
     * Stylesheets loaded on every page while the theme is active, relative to public/.
     *
     * @return array<string>
     */
    public function getStylesheets(): array
    {
        return ['style.css'];
    }
}
