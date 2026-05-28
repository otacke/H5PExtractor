<?php

/*
 * Removes h5p/h5p-core's `autoload.files` entries from Composer's generated
 * autoloader. H5PExtractor only consumes the CSS assets under
 * vendor/h5p/h5p-core/styles/; the PHP classes shipped alongside would
 * otherwise be `require`d on every request (and risk colliding with a host
 * application that has its own H5P core).
 *
 * Wired in composer.json as a `post-autoload-dump` script. We cannot use
 * `pre-autoload-dump`: by the time that event fires, Composer has already
 * read each package's autoload metadata into memory, so editing
 * installed.json there only takes effect on the *next* dump.
 *
 * Strategy:
 *  1. Strip the `autoload` block from vendor/composer/installed.json so any
 *     future `composer dump-autoload` is clean without rerunning this script.
 *  2. Strip the six h5p-core entries from the just-generated autoload_static.php
 *     and autoload_files.php so the current dump is also clean.
 *
 * Step (2) uses regex against Composer 2.x's generated file format. If a
 * future Composer release changes that format and the regex stops matching
 * the expected number of entries, the script fails loudly rather than
 * silently leaving the host application with unexpected autoloads.
 */

const PACKAGE = 'h5p/h5p-core';
const EXPECTED_FILE_COUNT = 6;

$vendorDir = __DIR__ . '/../vendor';

$installedJsonStripped = stripFromInstalledJson($vendorDir . '/composer/installed.json');

$staticStripped = stripFromAutoloadStatic($vendorDir . '/composer/autoload_static.php');
$filesStripped = stripFromAutoloadFiles($vendorDir . '/composer/autoload_files.php');

if ($installedJsonStripped) {
    // installed.json had the autoload section, so the just-generated autoload
    // files should also have contained the matching entries. Fail loudly if
    // the count is off — Composer's output format has likely changed.
    if ($staticStripped !== EXPECTED_FILE_COUNT || ($filesStripped !== null && $filesStripped !== EXPECTED_FILE_COUNT)) {
        fwrite(
            STDERR,
            sprintf(
                "[strip-h5p-core-autoload] Expected %d entries in each generated autoload file; got %d (static) and %s (files). Aborting — Composer's output format may have changed.\n",
                EXPECTED_FILE_COUNT,
                $staticStripped,
                $filesStripped === null ? 'n/a' : $filesStripped
            )
        );
        exit(1);
    }
    fwrite(STDOUT, sprintf("Stripped %s autoload entries from generated autoloader\n", PACKAGE));
    return;
}

// installed.json was already stripped on a previous run — subsequent dumps
// produce clean autoload files naturally, so finding zero entries is the
// expected no-op case.
if ($staticStripped !== 0 || ($filesStripped !== null && $filesStripped !== 0)) {
    fwrite(
        STDERR,
        sprintf(
            "[strip-h5p-core-autoload] installed.json had no %s autoload section, but generated files still contained %d (static) / %s (files) entries. Aborting — state is inconsistent.\n",
            PACKAGE,
            $staticStripped,
            $filesStripped === null ? 'n/a' : $filesStripped
        )
    );
    exit(1);
}

function stripFromInstalledJson(string $path): bool
{
    if (!file_exists($path)) {
        return false;
    }
    $data = json_decode(file_get_contents($path), true);
    if (!is_array($data) || !isset($data['packages']) || !is_array($data['packages'])) {
        return false;
    }
    $modified = false;
    foreach ($data['packages'] as &$package) {
        if (($package['name'] ?? null) === PACKAGE && isset($package['autoload'])) {
            unset($package['autoload']);
            $modified = true;
        }
    }
    unset($package);
    if ($modified) {
        file_put_contents(
            $path,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n"
        );
    }
    return $modified;
}

function stripFromAutoloadStatic(string $path): int
{
    if (!file_exists($path)) {
        return 0;
    }
    $original = file_get_contents($path);
    // Match lines like:  '<32-hex-hash>' => __DIR__ . '/..' . '/h5p/h5p-core/<file>.php',
    $pattern = "#^\s*'[a-f0-9]{32}'\s*=>\s*__DIR__\s*\.\s*'/\\.\\.'\s*\.\s*'/" . preg_quote(PACKAGE, '#') . "/[^']+'\s*,\s*\r?\n#m";
    $updated = preg_replace($pattern, '', $original, -1, $count);
    if ($count > 0) {
        file_put_contents($path, $updated);
    }
    return $count;
}

function stripFromAutoloadFiles(string $path): ?int
{
    // autoload_files.php is only generated if at least one `files` autoload
    // entry exists across all packages. Once we strip h5p-core's entries via
    // installed.json on a future dump, the file may stop being generated at
    // all. Return null in that case so the caller knows to skip the check.
    if (!file_exists($path)) {
        return null;
    }
    $original = file_get_contents($path);
    // Match lines like:  '<32-hex-hash>' => $vendorDir . '/h5p/h5p-core/<file>.php',
    $pattern = "#^\s*'[a-f0-9]{32}'\s*=>\s*\\\$vendorDir\s*\.\s*'/" . preg_quote(PACKAGE, '#') . "/[^']+'\s*,\s*\r?\n#m";
    $updated = preg_replace($pattern, '', $original, -1, $count);
    if ($count > 0) {
        file_put_contents($path, $updated);
    }
    return $count;
}
