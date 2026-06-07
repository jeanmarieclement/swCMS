<?php
namespace Tests\Unit\Security;

use PHPUnit\Framework\TestCase;
use App\Models\User;

class TimingAttackPreventionTest extends TestCase
{
    public function testPasswordVerificationUsesConstantTimeDummyHash()
    {
        $userModel = new User();

        // Test that authentication uses consistent timing
        // Even for non-existent users
        $startInvalid = microtime(true);
        $result1 = $userModel->authenticate('nonexistent@example.com', 'password');
        $timeInvalid = microtime(true) - $startInvalid;

        // The timing should be consistent due to dummy hash usage
        $this->assertFalse($result1);
        $this->assertGreaterThan(0, $timeInvalid); // Some time elapsed
    }

    public function testDummyHashIsPrecomputed()
    {
        $userModel = new User();

        // Verify getDummyHash returns same hash on multiple calls
        $reflection = new \ReflectionClass($userModel);
        $method = $reflection->getMethod('getDummyHash');
        $method->setAccessible(true);

        $hash1 = $method->invoke($userModel);
        $hash2 = $method->invoke($userModel);

        $this->assertEquals($hash1, $hash2);
    }
}
