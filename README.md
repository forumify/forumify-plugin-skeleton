# forumify plugin skeleton

Generates a ready-to-develop [forumify](https://forumify.net) plugin: quality tooling, a
test suite wired up to forumify's test kit, GitHub Actions workflows, and a README that
explains how to get going.

## Usage

```bash
composer create-project forumify/plugin-skeleton my-plugin
```

The generator asks for your package name, namespace, license and which parts you want to
start with, then rewrites the skeleton into your plugin and removes itself. Everything you
decline is simply not generated.

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
SKELETON_FEATURES=entities,frontend,tests,ci \
composer create-project forumify/plugin-skeleton my-plugin --no-interaction
```

| Variable | Answers |
| --- | --- |
| `SKELETON_PACKAGE` | package name (`vendor/name`) |
| `SKELETON_NAMESPACE` | PHP namespace |
| `SKELETON_PLUGIN_CLASS` | plugin class name |
| `SKELETON_NAME` | display name |
| `SKELETON_DESCRIPTION` | description |
| `SKELETON_AUTHOR` | author |
| `SKELETON_HOMEPAGE` | homepage |
| `SKELETON_LICENSE` | license |
| `SKELETON_PLATFORM` | minimum forumify version |
| `SKELETON_FEATURES` | comma separated list of the features to include, everything else is left out |

Valid features: `entities`, `admin`, `frontend`, `api`, `assets`, `tests`, `ci`.

## Requirements

- PHP 8.4 or newer
- Composer 2
- A MySQL 8.4 database, if you generate a test suite

## What you get

| Question | What it generates |
| --- | --- |
| Database entities | An example entity, its repository and a first migration |
| Admin section | An admin page, a permission, and an entry in the admin settings menu |
| Frontend page | A controller, route and template your members can visit |
| API Platform resources | API operations on the example entity, plus API tests |
| Stimulus assets | An example Stimulus controller and eslint config |
| Test suite | phpunit, wired to `Forumify\Testing`, with unit and application examples |
| GitHub Actions | Workflows running the tests and the quality checks |

Always included: `composer.json` with the plugin wiring, the plugin class, service and
route configuration, `phpcs.xml`, `phpstan.neon` (level 8), a `Makefile`, `.editorconfig`
and a README for the generated plugin.

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
| `plugin-skeleton` / `plugin_skeleton` | slugged and snake_cased forms |

Optional parts are wrapped in markers that are stripped when you decline them, and kept
(without the markers) when you accept:

```php
// skeleton:if admin
public function getPermissions(): array { /* ... */ }
// skeleton:endif
```

`// skeleton:if !admin` inverts the condition. The comment character is ignored, so the
same markers work in PHP, YAML, Twig, Makefiles and Markdown.

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
