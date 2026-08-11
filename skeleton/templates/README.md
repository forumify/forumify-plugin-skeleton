# Plugin Skeleton

A starting point for building forumify plugins.

> Generated with the forumify plugin skeleton. See the
> [plugin guide](https://docs.forumify.net/guides/plugin) to get your bearings, then
> replace this section with something about your own plugin.

## Requirements

- PHP 8.4 or newer
- Composer
- A MySQL 8.4 database, for running the tests

## What's in here

```
src/                      Your plugin's code
  Forumify\PluginSkeleton\ForumifyPluginSkeletonPlugin.php   Plugin class: name, author, permissions
config/
  services.yaml           Everything in src/ is registered as a service
  routes.yaml             Where your controllers' routes are mounted
templates/                Twig templates, referenced as @ForumifyPluginSkeletonPlugin/...
translations/             Translation files
migrations/               Database migrations
tests/                    Your test suite, see "Testing" below
```

Your plugin is a Symfony bundle. forumify finds it through `type: forumify-plugin` and
`extra.forumify-plugin-class` in `composer.json`, and loads everything in `config/`
automatically. Entities in `src/Entity`, migrations in `migrations/` and API resources are
registered for you based on where the plugin is installed, so you never write a vendor
path yourself.

## Developing against a forumify instance

Point a local forumify instance at your working copy with a path repository, so your
changes are live without publishing anything:

```bash
cd /path/to/your/forumify-instance
composer config repositories.plugin-skeleton path /path/to/plugin-skeleton
composer require forumify/plugin-skeleton:@dev
```

Then activate it:

```bash
php bin/console forumify:plugins:refresh
php bin/console forumify:plugins:activate forumify/plugin-skeleton
php bin/console cache:clear
```

Your plugin also shows up under **Admin → Plugins**, where it can be activated from the
interface instead.

## Testing

The test suite boots a real forumify application, so it needs a database. The connection
string lives in `.env` and defaults to `mysql://root:root@127.0.0.1:3306/plugin_test`.

```bash
make tests          # prepare the database, then run phpunit
make run-tests      # just phpunit, when the database is already set up
```

- `tests/Tests/Unit` — no kernel, no database, fast.
- `tests/Tests/Application` — boots forumify and makes real requests.

forumify ships the plumbing: the kernel, the test configuration, and factories for its own
entities under `Forumify\Testing\Factories`. Your `tests/` directory only holds your own
tests, which is why there is so little in it.

## Code quality

```bash
make quality        # phpcs + phpstan
make quality-fix    # fix what phpcs can fix automatically
```

Both run in CI on every push and pull request.

## Migrations

After changing an entity, generate a migration:

```bash
make migration
```

Review the generated file before committing it. Keep migrations in the
`ForumifyPluginSkeletonPluginMigrations` namespace: forumify registers your migrations
directory under the namespace the files declare, and that name is stored in the database
as part of the executed versions. Renaming it later would make doctrine re-run migrations
that have already been applied.

## Publishing

1. Push the repository to GitHub.
2. Submit it to [Packagist](https://packagist.org/packages/submit).
3. Tag a release. Users install it with
   `composer require forumify/plugin-skeleton`.

## Documentation

- [Plugin guide](https://docs.forumify.net/guides/plugin)
- [Customization guides](https://docs.forumify.net/guides/customization/introduction)
- [Component framework](https://docs.forumify.net/guides/framework/components/box)
