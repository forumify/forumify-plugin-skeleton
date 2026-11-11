# forumify plugin skeleton

Generates a bare [forumify](https://forumify.net) plugin or theme: registered with the
platform, with quality tooling, GitHub Actions workflows and a README explaining where
things go. Plugins also get a test suite wired up to forumify's test kit.

No example controllers, entities or templates — nothing to delete before you start.

## Usage

```bash
composer create-project forumify/plugin-skeleton my-plugin
```

The generator asks whether you're building a plugin or a theme, your package name,
namespace, license and whether you want tests and CI, then rewrites the skeleton into
your plugin or theme and removes itself.

Add `--no-interaction` to take every default, deriving the package name from the target
directory. Useful in scripts:

```bash
composer create-project forumify/plugin-skeleton my-plugin --no-interaction
```

### Scripting the answers

Every question can be answered up front with an environment variable, which is how this
repository's own CI checks the generator:

```bash
SKELETON_PACKAGE=acme/todo-plugin \
SKELETON_NAME=Todo \
SKELETON_FEATURES=tests,ci \
composer create-project forumify/plugin-skeleton my-plugin --no-interaction

SKELETON_TYPE=theme \
SKELETON_PACKAGE=acme/dark-theme \
SKELETON_FEATURES=ci \
composer create-project forumify/plugin-skeleton my-theme --no-interaction
```

| Variable | Answers |
| --- | --- |
| `SKELETON_TYPE` | `plugin` (the default) or `theme` |
| `SKELETON_PACKAGE` | package name (`vendor/name`) |
| `SKELETON_NAMESPACE` | PHP namespace |
| `SKELETON_PLUGIN_CLASS` | plugin or theme class name |
| `SKELETON_NAME` | display name |
| `SKELETON_DESCRIPTION` | description |
| `SKELETON_AUTHOR` | author |
| `SKELETON_HOMEPAGE` | homepage |
| `SKELETON_LICENSE` | license |
| `SKELETON_PLATFORM` | minimum forumify version |
| `SKELETON_FEATURES` | comma separated list of the features to include, everything else is left out |

Valid features: `tests`, `ci`. Themes can't have `tests`, it is ignored for them.

## Requirements

- PHP 8.4 or newer
- Composer 2
- A MySQL 8.4 database, if you generate a test suite

## What you get

### Plugins

Always:

- `composer.json` with the plugin wiring (`type`, `extra.forumify-plugin-class`, autoload)
- the plugin class, plus service and route configuration
- `phpcs.xml` and `phpstan.neon` (level 8), runnable with `make quality`
- a `Makefile`, `.editorconfig`, `.gitignore` and a `migrations/` directory
- a README explaining where controllers, entities, templates and translations go

Optionally:

| Question | What it generates |
| --- | --- |
| Test suite | phpunit wired to `Forumify\Testing`, plus a test proving the plugin boots |
| GitHub Actions | Workflows running the tests and the quality checks |

### Themes

- `composer.json` with the theme wiring (`type: forumify-theme`, `extra.forumify-plugin-class`, autoload)
- a theme class extending forumify's base theme, so every variable forumify's own styles
  rely on is there from the start and editable under Admin → Settings → Themes
- `public/style.css`, loaded on every page while the theme is active
- an empty `templates/` directory for overriding forumify's and plugins' templates
- `phpcs.xml` and `phpstan.neon`, a `Makefile`, `.editorconfig` and `.gitignore`
- a README covering variables, static files and template overrides
- optionally, a GitHub Actions workflow running the quality checks

Themes get no test suite: forumify's test kit boots plugins as Symfony bundles, and a
theme isn't one. They get no `config/` or `migrations/` either, forumify never loads them
for a theme.

## How this repository works

The skeleton is a working forumify plugin in its own right. Its test suite runs in CI
exactly as a generated plugin's would, which is what stops the templates from drifting
away from the platform.

Generation is plain string replacement of the skeleton's own identity:

| Placeholder | Becomes |
| --- | --- |
| `forumify/plugin-skeleton` | your package name |
| `Forumify\PluginSkeleton` | your namespace |
| `ForumifyPluginSkeletonPlugin` | your plugin class |
| `Plugin Skeleton` | your plugin's display name |
| `plugin-skeleton` / `plugin_skeleton` | slugged and snake_cased forms of the display name |

Optional parts are wrapped in markers that are stripped when you decline them, and kept
(without the markers) when you accept:

```makefile
# skeleton:if tests
tests:
	make setup-tests
	make run-tests
# skeleton:endif
```

`# skeleton:if !tests` inverts the condition. The comment character is ignored, so the
same markers work in PHP, YAML, Twig, Makefiles and Markdown. Besides the features,
`plugin` and `theme` work as conditions too.

Themes are generated from the same plugin: the generator removes what only a plugin
uses, then copies [`skeleton/theme`](skeleton/theme) over the top. That directory holds
the theme class (under the plugin class's placeholder name, so it is renamed like one),
`public/`, `templates/` and the theme's README. The plugin's README lives in
[`skeleton/plugin`](skeleton/plugin) and is copied the same way.

The generator lives in [`skeleton/Installer.php`](skeleton/Installer.php) and runs from
composer's `post-create-project-cmd`. It deletes itself once it is done.

## Contributing

Test a change by generating a plugin from your working copy:

```bash
composer create-project forumify/plugin-skeleton /tmp/test-plugin \
  --repository='{"type":"path","url":"/path/to/plugin-skeleton","options":{"symlink":false}}' \
  --stability=dev
cd /tmp/test-plugin && make quality && make tests
```

For a theme, add `SKELETON_TYPE=theme` in front and run only `make quality`.
