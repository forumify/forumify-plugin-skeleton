<?php

declare(strict_types=1);

namespace PluginSkeleton;

use Composer\IO\IOInterface;
use Composer\Script\Event;

/**
 * Turns this skeleton into a plugin of your own.
 *
 * Runs automatically after `composer create-project forumify/plugin-skeleton`. It asks
 * for the things it cannot guess, rewrites the skeleton's placeholders, drops the parts
 * you said you don't need, and then deletes itself.
 */
final class Installer
{
    /**
     * Placeholders as they appear in the skeleton's files. The skeleton is a working
     * plugin in its own right, which is what keeps these from going stale: its test
     * suite runs in CI exactly as a generated plugin's would.
     */
    private const PACKAGE = 'forumify/plugin-skeleton';
    private const NAMESPACE = 'Forumify\\PluginSkeleton';
    private const PLUGIN_CLASS = 'ForumifyPluginSkeletonPlugin';
    private const ROUTE_PREFIX = 'forumify_plugin_skeleton';
    private const SLUG = 'plugin-skeleton';
    private const SNAKE = 'plugin_skeleton';
    private const TABLE = 'plugin_skeleton_example';
    private const DISPLAY_NAME = 'Plugin Skeleton';
    private const DESCRIPTION = 'A starting point for building forumify plugins.';
    // Deliberately distinctive: a placeholder like "forumify" would also match
    // forumify/forumify-platform and every @ForumifyBundle reference.
    private const AUTHOR = 'Skeleton Author';
    private const HOMEPAGE = 'https://plugin.example.com';

    /** Paths removed when the matching feature is not wanted. */
    private const FEATURE_PATHS = [
        'entities' => [
            'src/Entity',
            'src/Repository',
            'migrations',
            'tests/Tests/Unit/EntityTest.php',
            'tests/Tests/Factories/ExampleFactory.php',
        ],
        'admin' => ['src/Admin', 'src/Form', 'templates/admin'],
        'frontend' => ['src/Controller', 'templates/frontend', 'tests/Tests/Application/FrontendControllerTest.php'],
        'api' => ['tests/Tests/Application/ApiTest.php'],
        'assets' => ['assets'],
        // .env only configures the test application, nothing else reads it.
        'tests' => ['tests', 'phpunit.xml.dist', '.env', '.github/workflows/tests.yml'],
        'ci' => ['.github'],
    ];

    private const FEATURE_QUESTIONS = [
        'entities' => 'Database entities, repositories and migrations',
        'admin' => 'An admin section, with a permission and a settings form',
        'frontend' => 'A page your members can visit',
        'api' => 'API Platform resources',
        'assets' => 'Stimulus controllers and other frontend assets',
        'tests' => 'A test suite (phpunit, wired up to forumify\'s test kit)',
        'ci' => 'GitHub Actions workflows for tests and code quality',
    ];

    /**
     * Files that are never part of a generated plugin. The skeleton's own README is not
     * listed: installReadme() overwrites it with the generated plugin's README.
     */
    private const SKELETON_ONLY = ['skeleton', '.github/workflows/skeleton.yml'];

    public static function run(Event $event): void
    {
        $io = $event->getIO();
        $root = \dirname($event->getComposer()->getConfig()->get('vendor-dir'));

        $installer = new self($io, $root);

        $io->write('');
        $io->write('<info>Setting up your forumify plugin</info>');
        $io->write('Press enter to accept the [default] shown for each question.');
        $io->write('');

        try {
            $installer->install();
        } catch (\Throwable $e) {
            $io->writeError('<error>Could not finish setting up the plugin: ' . $e->getMessage() . '</error>');
            $io->writeError('The skeleton has been left untouched so you can try again with:');
            $io->writeError('  composer run post-create-project-cmd');

            throw $e;
        }
    }

    private function __construct(
        private readonly IOInterface $io,
        private readonly string $root,
    ) {
    }

    private function install(): void
    {
        $package = $this->askPackage();
        [$vendor, $shortName] = explode('/', $package);

        $namespace = $this->askNamespace($vendor, $shortName);
        $pluginClass = $this->askPluginClass($vendor, $shortName);
        $displayName = $this->ask('Plugin name, as your users will see it', $this->titleize($shortName), 'name');
        $description = $this->ask('Description', "$displayName plugin for forumify", 'description');
        $author = $this->ask('Author', $vendor, 'author');
        $homepage = $this->ask('Homepage', '', 'homepage');
        $license = $this->askLicense();
        $platform = $this->ask('Minimum forumify version', '^1.3', 'platform');
        $features = $this->askFeatures();

        $replacements = [
            // The class name first: it starts with the same characters as the namespace
            // token once the separators are stripped.
            self::PLUGIN_CLASS => $pluginClass,
            self::PACKAGE => $package,
            self::NAMESPACE . '\\\\' => str_replace('\\', '\\\\', $namespace) . '\\\\',
            self::NAMESPACE => $namespace,
            self::ROUTE_PREFIX => $this->snake($package),
            self::TABLE => $this->snake($displayName) . '_example',
            self::SLUG => $this->slug($displayName),
            self::SNAKE => $this->snake($displayName),
            self::DISPLAY_NAME => $displayName,
            self::DESCRIPTION => $description,
            self::AUTHOR => $author,
            // Quoted separately so an omitted homepage becomes null rather than ''.
            "'" . self::HOMEPAGE . "'" => $homepage === '' ? 'null' : "'$homepage'",
            self::HOMEPAGE => $homepage,
        ];

        $this->io->write('');
        $this->io->write('Generating <info>' . $package . '</info>...');

        $this->removeUnwantedFeatures($features);
        $this->applyToAllFiles($replacements, $features);
        $this->renamePluginClass($pluginClass);
        $this->writeComposerJson($package, $description, $license, $homepage, $namespace, $pluginClass, $platform, $features);
        $this->installReadme();
        $this->writeLicense($license, $author);
        $this->removeSkeleton();
        $this->refreshAutoloader();
        $this->formatGeneratedCode();
        $this->initialiseGit();

        $this->writeNextSteps($package, $features);
    }

    private function askPackage(): string
    {
        $default = 'acme/' . basename($this->root);

        while (true) {
            $package = strtolower($this->ask('Package name (vendor/name)', $default, 'package'));

            if (preg_match('#^[a-z0-9]([_.-]?[a-z0-9]+)*/[a-z0-9](([_.]|-{1,2})?[a-z0-9]+)*$#', $package) === 1) {
                return $package;
            }

            $this->io->writeError('<error>"' . $package . '" is not a valid composer package name, expected something like "acme/todo-plugin".</error>');

            if (!$this->io->isInteractive()) {
                throw new \RuntimeException('Invalid package name in non-interactive mode.');
            }
        }
    }

    private function askNamespace(string $vendor, string $shortName): string
    {
        $default = $this->studly($vendor) . '\\' . $this->studly($this->stripPluginSuffix($shortName));

        while (true) {
            $namespace = trim($this->ask('PHP namespace', $default, 'namespace'), '\\');

            if (preg_match('#^[A-Za-z_][A-Za-z0-9_]*(\\\\[A-Za-z_][A-Za-z0-9_]*)*$#', $namespace) === 1) {
                return $namespace;
            }

            $this->io->writeError('<error>"' . $namespace . '" is not a valid PHP namespace.</error>');

            if (!$this->io->isInteractive()) {
                throw new \RuntimeException('Invalid namespace in non-interactive mode.');
            }
        }
    }

    private function askPluginClass(string $vendor, string $shortName): string
    {
        $default = $this->studly($vendor) . $this->studly($this->stripPluginSuffix($shortName)) . 'Plugin';

        return $this->ask('Plugin class name', $default, 'plugin_class');
    }

    private function askLicense(): string
    {
        $licenses = ['OSL-3.0', 'MIT', 'proprietary'];

        $override = $this->env('license');
        if ($override !== null) {
            return $override;
        }

        if (!$this->io->isInteractive()) {
            return $licenses[0];
        }

        $choice = $this->io->select(
            'License [<comment>OSL-3.0</comment>]: ',
            $licenses,
            '0',
            false,
            'Pick 0, 1 or 2.',
        );

        return $licenses[(int)$choice];
    }

    /**
     * @return array<string, bool>
     */
    private function askFeatures(): array
    {
        $selected = $this->env('features');
        if ($selected !== null) {
            $wanted = array_filter(array_map('trim', explode(',', $selected)));

            return array_map(
                static fn (string $feature): bool => \in_array($feature, $wanted, true),
                array_combine(array_keys(self::FEATURE_QUESTIONS), array_keys(self::FEATURE_QUESTIONS)),
            );
        }

        $this->io->write('');
        $this->io->write('<info>What should the plugin start with?</info>');
        $this->io->write('Anything you leave out is simply not generated, you can always add it later.');

        $features = [];
        foreach (self::FEATURE_QUESTIONS as $feature => $question) {
            // The api example exposes the entity, so it is pointless on its own.
            if ($feature === 'api' && !($features['entities'] ?? false)) {
                $features[$feature] = false;
                continue;
            }

            $features[$feature] = $this->confirm($question, true);
        }

        return $features;
    }

    /**
     * @param array<string, bool> $features
     */
    private function removeUnwantedFeatures(array $features): void
    {
        foreach ($features as $feature => $enabled) {
            if ($enabled) {
                continue;
            }

            foreach (self::FEATURE_PATHS[$feature] ?? [] as $path) {
                $this->remove($this->root . '/' . $path);
            }
        }
    }

    /**
     * @param array<string, string> $replacements
     * @param array<string, bool> $features
     */
    private function applyToAllFiles(array $replacements, array $features): void
    {
        foreach ($this->files() as $file) {
            $contents = file_get_contents($file);
            if ($contents === false || !$this->isText($contents)) {
                continue;
            }

            $updated = $this->stripFeatureBlocks($contents, $features);
            $updated = strtr($updated, $replacements);

            if ($updated !== $contents) {
                file_put_contents($file, $updated);
            }
        }
    }

    /**
     * Removes sections the generated plugin has no use for. Blocks look like:
     *
     *     # skeleton:if entities
     *     ...
     *     # skeleton:endif
     *
     * A leading "!" inverts the condition. The comment character doesn't matter, which
     * lets the same markers work in php, yaml, twig and markdown alike.
     *
     * @param array<string, bool> $features
     */
    private function stripFeatureBlocks(string $contents, array $features): string
    {
        // The leading (\n[ \t]*)? swallows the blank line above a removed block, so
        // dropping one doesn't leave a gap behind.
        $pattern = '/(\n[ \t]*)?^[^\n]*skeleton:if (!?)([a-z]+)[^\n]*\n(.*?)^[^\n]*skeleton:endif[^\n]*\n/ms';

        $replace = static function (array $matches) use ($features): string {
            [, $blankLine, $negated, $feature, $body] = $matches;

            $enabled = $features[$feature] ?? false;
            $keep = $negated === '!' ? !$enabled : $enabled;

            return $keep ? $blankLine . $body : '';
        };

        // Repeat so blocks nested inside a kept block are handled too.
        do {
            $previous = $contents;
            $contents = preg_replace_callback($pattern, $replace, $contents) ?? $contents;
        } while ($contents !== $previous);

        return $contents;
    }

    private function renamePluginClass(string $pluginClass): void
    {
        $current = $this->root . '/src/' . self::PLUGIN_CLASS . '.php';
        if (is_file($current)) {
            rename($current, $this->root . '/src/' . $pluginClass . '.php');
        }
    }

    /**
     * @param array<string, bool> $features
     */
    private function writeComposerJson(
        string $package,
        string $description,
        string $license,
        string $homepage,
        string $namespace,
        string $pluginClass,
        string $platform,
        array $features,
    ): void {
        $path = $this->root . '/composer.json';
        /** @var array<string, mixed> $composer */
        $composer = json_decode((string)file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

        $composer['name'] = $package;
        $composer['description'] = $description;
        $composer['license'] = $license;
        if ($homepage !== '') {
            $composer['homepage'] = $homepage;
        }

        $composer['require']['forumify/forumify-platform'] = $platform;
        $composer['autoload']['psr-4'] = [$namespace . '\\' => 'src/'];
        $composer['extra']['forumify-plugin-class'] = $namespace . '\\' . $pluginClass;

        if (!$features['tests']) {
            unset($composer['autoload-dev']);
            foreach (self::testPackages() as $testPackage) {
                unset($composer['require-dev'][$testPackage]);
            }
        }

        // The skeleton's own wiring has no place in a generated plugin.
        unset($composer['scripts'], $composer['autoload-dev']['psr-4']['PluginSkeleton\\']);
        if (isset($composer['autoload-dev']['psr-4']) && $composer['autoload-dev']['psr-4'] === []) {
            unset($composer['autoload-dev']);
        }

        file_put_contents($path, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
    }

    /**
     * @return array<int, string>
     */
    private static function testPackages(): array
    {
        return [
            'dama/doctrine-test-bundle',
            'dg/bypass-finals',
            'phpunit/phpunit',
            'symfony/browser-kit',
            'symfony/css-selector',
            'zenstruck/foundry',
        ];
    }

    /**
     * The skeleton ships the OSL-3.0 text forumify itself uses. Anything else gets the
     * matching text, or no license file at all.
     */
    private function writeLicense(string $license, string $author): void
    {
        if ($license === 'OSL-3.0') {
            return;
        }

        $path = $this->root . '/LICENSE.md';

        if ($license !== 'MIT') {
            $this->remove($path);
            return;
        }

        $year = date('Y');
        file_put_contents($path, <<<LICENSE
            MIT License

            Copyright (c) $year $author

            Permission is hereby granted, free of charge, to any person obtaining a copy
            of this software and associated documentation files (the "Software"), to deal
            in the Software without restriction, including without limitation the rights
            to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
            copies of the Software, and to permit persons to whom the Software is
            furnished to do so, subject to the following conditions:

            The above copyright notice and this permission notice shall be included in all
            copies or substantial portions of the Software.

            THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
            IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
            FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
            AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
            LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
            OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
            SOFTWARE.

            LICENSE);
    }

    private function installReadme(): void
    {
        $template = $this->root . '/skeleton/templates/README.md';
        if (is_file($template)) {
            rename($template, $this->root . '/README.md');
        }
    }

    private function removeSkeleton(): void
    {
        foreach (self::SKELETON_ONLY as $path) {
            $this->remove($this->root . '/' . $path);
        }
    }

    /**
     * Removing optional blocks leaves whitespace behind that the coding standard rejects,
     * for example an argument list that lost its only argument. Rather than trying to
     * generate perfect whitespace for every combination, let phpcbf sort it out.
     */
    private function formatGeneratedCode(): void
    {
        $phpcbf = $this->root . '/vendor/bin/phpcbf';
        if (!is_file($phpcbf)) {
            return;
        }

        $command = sprintf(
            '%s %s --standard=%s --no-cache -q 2>&1',
            escapeshellarg(PHP_BINARY),
            escapeshellarg($phpcbf),
            escapeshellarg($this->root . '/phpcs.xml'),
        );

        // phpcbf exits non-zero when it fixed something, which is the expected outcome.
        exec('cd ' . escapeshellarg($this->root) . ' && ' . $command, $output, $exitCode);
    }

    /**
     * composer.json now describes a different package under a different namespace, so the
     * generated autoloader and installed.php have to be rebuilt before anything can boot.
     */
    private function refreshAutoloader(): void
    {
        $composerBinary = (string)getenv('COMPOSER_BINARY');
        if ($composerBinary === '') {
            $this->io->writeError('<comment>Could not find composer, run "composer dump-autoload" yourself before using the plugin.</comment>');
            return;
        }

        $command = sprintf(
            '%s %s dump-autoload --working-dir=%s --quiet 2>&1',
            escapeshellarg(PHP_BINARY),
            escapeshellarg($composerBinary),
            escapeshellarg($this->root),
        );

        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            $this->io->writeError('<comment>Could not regenerate the autoloader, run "composer dump-autoload" yourself.</comment>');
        }
    }

    private function initialiseGit(): void
    {
        if (is_dir($this->root . '/.git')) {
            return;
        }

        if (!$this->confirm('Initialise a git repository', true)) {
            return;
        }

        exec('git init --quiet ' . escapeshellarg($this->root) . ' 2>&1', $output, $exitCode);
        if ($exitCode !== 0) {
            $this->io->writeError('<comment>Could not initialise a git repository, carry on without it.</comment>');
        }
    }

    /**
     * @param array<string, bool> $features
     */
    private function writeNextSteps(string $package, array $features): void
    {
        $this->io->write('');
        $this->io->write('<info>Done.</info> Your plugin lives in ' . $this->root);
        $this->io->write('');
        $this->io->write('Next steps:');
        $this->io->write('  1. Read README.md, it explains the layout and how to run the plugin on a forumify instance.');

        if ($features['tests']) {
            $this->io->write('  2. Start a database, then run <comment>make tests</comment>.');
            $this->io->write('  3. Run <comment>make quality</comment> before you commit.');
        } else {
            $this->io->write('  2. Run <comment>make quality</comment> before you commit.');
        }

        $this->io->write('');
        $this->io->write('Install it into a forumify instance with a path repository, see README.md.');
        $this->io->write('Documentation: <comment>https://docs.forumify.net/guides/plugin</comment>');
        $this->io->write('');
        unset($package);
    }

    /**
     * Every file in the project, skipping directories composer and git own.
     *
     * @return iterable<string>
     */
    private function files(): iterable
    {
        $directory = new \RecursiveDirectoryIterator($this->root, \FilesystemIterator::SKIP_DOTS);
        $filter = new \RecursiveCallbackFilterIterator(
            $directory,
            static function (\SplFileInfo $file): bool {
                $name = $file->getFilename();

                return !\in_array($name, ['vendor', '.git', 'node_modules', 'var'], true);
            },
        );

        /** @var \SplFileInfo $file */
        foreach (new \RecursiveIteratorIterator($filter) as $file) {
            if ($file->isFile()) {
                yield $file->getPathname();
            }
        }
    }

    private function isText(string $contents): bool
    {
        return $contents === '' || !str_contains(substr($contents, 0, 1024), "\0");
    }

    private function remove(string $path): void
    {
        if (is_link($path) || is_file($path)) {
            unlink($path);
            return;
        }

        if (!is_dir($path)) {
            return;
        }

        /** @var \SplFileInfo $file */
        foreach (new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        ) as $file) {
            $file->isDir() && !$file->isLink() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }

        rmdir($path);
    }

    /**
     * @param string|null $envKey Environment variable that answers this question without
     *      asking, so generation can be scripted. See the README for the full list.
     */
    private function ask(string $question, string $default, ?string $envKey = null): string
    {
        $override = $envKey === null ? null : $this->env($envKey);
        if ($override !== null) {
            return $override;
        }

        $suffix = $default === '' ? ' (optional): ' : ' [<comment>' . $default . '</comment>]: ';
        $answer = $this->io->ask($question . $suffix, $default);

        return trim((string)$answer);
    }

    private function env(string $key): ?string
    {
        $value = getenv('SKELETON_' . strtoupper($key));

        return $value === false ? null : trim($value);
    }

    private function confirm(string $question, bool $default): bool
    {
        return $this->io->askConfirmation(
            $question . ' [<comment>' . ($default ? 'Y/n' : 'y/N') . '</comment>]: ',
            $default,
        );
    }

    private function stripPluginSuffix(string $name): string
    {
        return preg_replace('/[-_]?plugin$/i', '', $name) ?: $name;
    }

    private function studly(string $value): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_', '.'], ' ', $value)));
    }

    private function slug(string $value): string
    {
        return trim((string)preg_replace('/[^a-z0-9]+/i', '-', strtolower($value)), '-');
    }

    private function snake(string $value): string
    {
        return strtolower((string)preg_replace('/[^a-z0-9]+/i', '_', $value));
    }

    private function titleize(string $value): string
    {
        return ucwords(str_replace(['-', '_'], ' ', $this->stripPluginSuffix($value)));
    }
}
