<?php

namespace App\Core;

/**
 * Reader for the .env file
 *
 * parse_ini_file() cannot be pointed at a .env directly: PHP's ini parser only
 * accepts ';' as a comment marker, so the '#' comments every .env convention
 * uses are parsed as values and a line like "# Page Cache (Smarty)" aborts the
 * whole file with a syntax error. The result is the worst kind of failure for
 * the audience this project targets — the file is ignored in silence, the
 * defaults take over, and the site reports only that it cannot reach the
 * database.
 *
 * So comments are stripped here before the remainder is handed to the ini
 * parser.
 *
 * @package App\Core
 */
class EnvFile
{
    /**
     * Read a .env file into a name => value map
     *
     * @param string $path
     * @return array Empty when the file is missing, unreadable or unparseable
     */
    public static function parse($path)
    {
        if (!is_file($path) || !is_readable($path)) {
            return [];
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            return [];
        }

        $parsed = @parse_ini_string(self::stripComments($contents), false, INI_SCANNER_NORMAL);

        if ($parsed === false) {
            // Not fatal: the caller falls back to defaults. Say so somewhere,
            // rather than leaving an admin to guess why their settings are
            // ignored.
            error_log('swCMS: could not parse ' . $path . ' — check for stray quotes or unescaped characters.');

            return [];
        }

        return $parsed;
    }

    /**
     * Drop whole-line comments, which the ini parser would choke on
     *
     * Only full-line comments: a '#' after a value can legitimately be part of
     * a password or a URL fragment, and guessing there would corrupt settings.
     *
     * @param string $contents
     * @return string
     */
    private static function stripComments($contents)
    {
        $lines = preg_split('/\R/', $contents);
        $kept = [];

        foreach ($lines as $line) {
            $trimmed = ltrim($line);

            if ($trimmed === '' || $trimmed[0] === '#' || $trimmed[0] === ';') {
                continue;
            }

            $kept[] = $line;
        }

        return implode("\n", $kept);
    }
}
