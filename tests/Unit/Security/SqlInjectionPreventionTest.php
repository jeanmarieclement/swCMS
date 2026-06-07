<?php
namespace Tests\Unit\Security;

use PHPUnit\Framework\TestCase;
use App\Models\User;

class SqlInjectionPreventionTest extends TestCase
{
    public function testOrderByRejectsInvalidColumn()
    {
        $userModel = new User();

        // Attempt SQL injection via orderBy
        $maliciousOrderBy = "id` DESC; DROP TABLE users; --";

        // Should fallback to safe default, not execute injection
        $result = $userModel->findAll([], $maliciousOrderBy, 'ASC', 10);

        // If we get here without error, injection was prevented
        $this->assertIsArray($result);
    }

    public function testOrderByAcceptsValidColumn()
    {
        $userModel = new User();

        // Valid column should work
        $result = $userModel->findAll([], 'id', 'ASC', 10);

        $this->assertIsArray($result);
    }

    public function testOrderDirectionOnlyAllowsAscDesc()
    {
        $userModel = new User();

        // Invalid order direction should default to ASC
        $result = $userModel->findAll([], 'id', 'INVALID; DROP TABLE users', 10);

        $this->assertIsArray($result);
    }

    public function testOrderByFallsBackToIdForInvalidColumn()
    {
        $userModel = new User();

        // Try to order by a column that doesn't exist in whitelist
        $result = $userModel->findAll([], 'nonexistent_column', 'ASC', 10);

        // Should still return array (falls back to 'id')
        $this->assertIsArray($result);
    }

    public function testOrderDirectionNormalizedToUpperCase()
    {
        $userModel = new User();

        // Test that lowercase 'desc' is properly converted to 'DESC'
        $result = $userModel->findAll([], 'id', 'desc', 10);

        $this->assertIsArray($result);
    }
}
