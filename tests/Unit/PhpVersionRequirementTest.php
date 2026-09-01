<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * The declared PHP requirement has to stay truthful.
 *
 * Most of the codebase uses match(), a parse error before PHP 8.0, so a site
 * installed on an older interpreter dies with a blank 500 rather than a usable
 * message. public/index.php guards against that, but only if the version it
 * guards on and the one composer.json advertises stay in step.
 */
class PhpVersionRequirementTest extends TestCase
{
    /**
     * Read the guard version out of index.php without executing the file.
     */
    private function guardVersion(): string
    {
        $source = file_get_contents(ROOT_PATH . '/public/index.php');
        $this->assertIsString($source, 'public/index.php should be readable');

        $matched = preg_match(
            "/define\(\s*'SWCMS_MIN_PHP'\s*,\s*'([^']+)'\s*\)/",
            $source,
            $matches
        );

        $this->assertSame(1, $matched, 'public/index.php should define SWCMS_MIN_PHP');

        return $matches[1];
    }

    public function testGuardRunsBeforeAnythingThatCouldFailToParse()
    {
        $source = file_get_contents(ROOT_PATH . '/public/index.php');

        $guardPosition = strpos($source, "define('SWCMS_MIN_PHP'");
        $firstRequire = strpos($source, 'require_once');

        $this->assertNotFalse($guardPosition);
        $this->assertNotFalse($firstRequire);
        $this->assertLessThan(
            $firstRequire,
            $guardPosition,
            'The version guard must run before any file that uses PHP 8 syntax is loaded'
        );
    }

    public function testComposerConstraintMatchesTheGuard()
    {
        $composer = json_decode(file_get_contents(ROOT_PATH . '/composer.json'), true);

        $this->assertArrayHasKey('php', $composer['require']);

        $constraint = $composer['require']['php'];
        $guard = $this->guardVersion();

        // e.g. '^8.0' has to admit '8.0.0' and nothing below it.
        [$major, $minor] = explode('.', $guard);

        $this->assertSame(
            '^' . $major . '.' . $minor,
            $constraint,
            'composer.json must require the same minimum PHP the runtime guard enforces'
        );
    }

    public function testTheRunningInterpreterSatisfiesTheRequirement()
    {
        $this->assertTrue(
            version_compare(PHP_VERSION, $this->guardVersion(), '>='),
            'Tests run on an interpreter below the declared minimum'
        );
    }
}
