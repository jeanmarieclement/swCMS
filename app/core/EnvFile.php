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

        $stripped = self::stripComments($contents);
        $parsed = @parse_ini_string($stripped, false, INI_SCANNER_NORMAL);

        if ($parsed !== false) {
            return $parsed;
        }

        // The ini parser rejects perfectly ordinary .env values — an unquoted
        // '=' inside a key or token, a bare '&' or '|' — and it rejects the
        // whole file, not the offending line. Falling back to a line reader
        // keeps one awkward value from silently wiping out the database
        // settings underneath it.
        $lines = self::parseLines($stripped);

        if ($lines === []) {
            error_log('swCMS: could not read any setting from ' . $path);
        }

        return $lines;
    }

    /**
     * Read KEY=VALUE lines without the ini parser's opinions
     *
     * @param string $contents Already stripped of comments
     * @return array
     */
    private static function parseLines($contents)
    {
        $values = [];

        foreach (preg_split('/\R/', $contents) as $line) {
            $separator = strpos($line, '=');

            if ($separator === false) {
                continue;
            }

            $name = trim(substr($line, 0, $separator));

            if ($name === '') {
                continue;
            }

            $values[$name] = self::unquote(trim(substr($line, $separator + 1)));
        }

        return $values;
    }

    /**
     * Strip one matching pair of surrounding quotes
     *
     * @param string $value
     * @return string
     */
    private static function unquote($value)
    {
        if (strlen($value) >= 2) {
            $first = $value[0];

            if (($first === '"' || $first === "'") && substr($value, -1) === $first) {
                return substr($value, 1, -1);
            }
        }

        return $value;
    }

    /**
     * Drop the comments, which the ini parser would choke on or swallow whole
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

            $kept[] = self::stripTrailingComment($line);
        }

        return implode("\n", $kept);
    }

    /**
     * Remove a trailing comment, leaving '#' inside a value alone
     *
     * A comment has to be introduced by whitespace — DB_PASS=p#ss is a password,
     * DB_PORT=3306 # the port is a comment — and a '#' inside quotes is part of
     * the value wherever it sits.
     *
     * @param string $line
     * @return string
     */
    private static function stripTrailingComment($line)
    {
        $quote = null;
        $length = strlen($line);

        for ($i = 0; $i < $length; $i++) {
            $character = $line[$i];

            if ($quote !== null) {
                if ($character === $quote) {
                    $quote = null;
                }

                continue;
            }

            if ($character === '"' || $character === "'") {
                $quote = $character;
                continue;
            }

            if ($character === '#' && $i > 0 && ($line[$i - 1] === ' ' || $line[$i - 1] === "\t")) {
                return rtrim(substr($line, 0, $i));
            }
        }

        return $line;
    }
}
