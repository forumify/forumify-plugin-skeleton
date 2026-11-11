# Plugin Skeleton

A starting point for building forumify plugins.

> Generated with the forumify plugin skeleton. See the
> [theme guide](https://docs.forumify.net/guides/theme) to get your bearings, then
> replace this section with something about your own theme.

## Requirements

- PHP 8.4 or newer
- Composer

## What's in here

```
src/
  ForumifyPluginSkeletonPlugin.php   Theme class: name, author, stylesheets, variables
public/
  style.css               Loaded on every page while the theme is active
templates/                Overrides for forumify's and plugins' templates
```

Two things in `composer.json` make it a theme:

```json
{
    "type": "forumify-theme",
    "extra": {
        "forumify-plugin-class": "Forumify\\PluginSkeleton\\ForumifyPluginSkeletonPlugin"
    }
}
```

forumify discovers installed themes by that package type, and reads the class named in
`extra` for the theme's name, stylesheets and variables.

## Variables

Your theme class extends forumify's base theme, so it starts out with every variable
forumify's own styles rely on: colours, elevations, borders and font size. Admins can
change all of them under **Admin → Settings → Themes**, with separate values for dark
mode.

Add your own by overriding `getThemeConfig()`, the docblock on your theme class shows
how. Each variable becomes a CSS custom property:

```css
.header {
    background-color: var(--c-header);
}
```

## Stylesheets and other static files

Everything in `public/` is linked into the forumify instance at
`public/themes/forumify/plugin-skeleton/` when the theme is installed or refreshed.
Stylesheets listed in `getStylesheets()` are loaded on every page, anything else
(images, fonts) can be referenced relative to them.

Using an asset builder for sass or similar? Keep the sources in `assets/` and build into
`public/`: only `public/` reaches the forumify instance.

## Overriding templates

Templates in `templates/` replace forumify's own. The first directory is `Forumify` for
core templates, or the plugin class name for a plugin's templates:

```
templates/Forumify/frontend/forum/list.html.twig
templates/AcmeCalendarPlugin/frontend/calendar.html.twig
```

Extend the original with the `@!` prefix and override only the blocks you need, so your
theme keeps working when forumify updates the template:

```twig
{% extends '@!Forumify/frontend/forum/list.html.twig' %}

{% block body %}
    <div class="flex gap-4">
        {{ parent() }}
    </div>
{% endblock %}
```

Missing a block you need? Add it to the original template and open a pull request on the
[platform repository](https://github.com/forumify/forumify-platform).

## Developing against a forumify instance

Point a local forumify instance at your working copy with a path repository, so your
changes are live without publishing anything:

```bash
cd /path/to/your/forumify-instance
composer config repositories.plugin-skeleton path /path/to/plugin-skeleton
composer require forumify/plugin-skeleton:@dev
```

The theme then shows up under **Admin → Settings → Themes**, where you can activate it.
If it doesn't, refresh the plugin list:

```bash
php bin/console forumify:plugins:refresh
```

## Code quality

```bash
make quality        # phpcs + phpstan
make quality-fix    # fix what phpcs can fix automatically
```

## Publishing

1. Push the repository to GitHub.
2. Submit it to [Packagist](https://packagist.org/packages/submit).
3. Tag a release. Users install it with
   `composer require forumify/plugin-skeleton`.

## Documentation

- [Theme guide](https://docs.forumify.net/guides/theme)
- [Customization guides](https://docs.forumify.net/guides/customization/introduction)
