<?php

namespace Tests\Unit\Database;

use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Migration method calls Test
 *
 * A migration that calls a method its class does not have fatals the moment
 * that branch runs — and a driver-specific branch only runs on that driver, so
 * the mistake stays invisible until someone migrates on the other one.
 *
 * @package Tests\Unit\Database
 */
class MigrationMethodCallsTest extends TestCase
{
    /**
     * @return array<int, array{0: string, 1: string}> [file, class] pairs
     */
    private function migrationClasses(): array
    {
        $found = [];

        foreach (glob(dirname(__DIR__, 3) . '/database/migrations/*.php') as $file) {
            $source = file_get_contents($file);

            if (!preg_match('/^class\s+([A-Za-z0-9_]+)/m', $source, $m)) {
                continue;
            }

            require_once $file;
            $found[] = [$file, $m[1]];
        }

        return $found;
    }

    public function testEveryMigrationCallsOnlyMethodsThatExist()
    {
        $migrations = $this->migrationClasses();

        $this->assertNotEmpty($migrations, 'No migrations were resolved — the test would prove nothing');

        $offenders = [];

        foreach ($migrations as [$file, $class]) {
            $this->assertTrue(class_exists($class), "Class {$class} not defined by " . basename($file));

            $source = file_get_contents($file);
            preg_match_all('/\$this->([A-Za-z0-9_]+)\s*\(/', $source, $calls);

            foreach (array_unique($calls[1]) as $method) {
                if (!method_exists($class, $method)) {
                    $offenders[] = basename($file) . ": \$this->{$method}() does not exist on {$class}";
                }
            }
        }

        $this->assertSame([], $offenders, "Migrations call methods that do not exist:\n" . implode("\n", $offenders));
    }

    public function testMigrationBaseClassHasNoExecMethod()
    {
        // Guards the assumption the test above relies on: `exec` is the easy
        // typo for `execute`, and it only exists on the PDO handle ($this->db).
        $base = new ReflectionClass(\App\Core\Database\Migration::class);

        $this->assertFalse($base->hasMethod('exec'));
        $this->assertTrue($base->hasMethod('execute'));
    }
}
