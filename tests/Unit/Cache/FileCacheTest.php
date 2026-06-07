<?php

namespace Tests\Unit\Cache;

use PHPUnit\Framework\TestCase;
use App\Cache\FileCache;

/**
 * FileCache Test
 * Tests for file-based cache implementation
 *
 * @package Tests\Unit\Cache
 * @author swCMS Team
 */
class FileCacheTest extends TestCase
{
    private FileCache $cache;
    private string $testCachePath;

    protected function setUp(): void
    {
        parent::setUp();

        // Create temporary cache directory for testing
        $this->testCachePath = sys_get_temp_dir() . '/swcms_test_cache_' . uniqid();
        @mkdir($this->testCachePath, 0755, true);

        // Create cache instance with test directory
        $this->cache = new FileCache($this->testCachePath, 3600);
    }

    protected function tearDown(): void
    {
        // Clean up test cache directory
        $this->cleanupCacheDirectory();
        parent::tearDown();
    }

    private function cleanupCacheDirectory(): void
    {
        if (!is_dir($this->testCachePath)) {
            return;
        }

        $files = glob($this->testCachePath . '/*.cache');
        if ($files) {
            foreach ($files as $file) {
                @unlink($file);
            }
        }
        @rmdir($this->testCachePath);
    }

    public function testSetStoresValueInCache()
    {
        $result = $this->cache->set('test_key', 'test_value');

        $this->assertTrue($result);
        $this->assertTrue($this->cache->has('test_key'));
    }

    public function testGetRetrievesStoredValue()
    {
        $this->cache->set('test_key', 'test_value');
        $result = $this->cache->get('test_key');

        $this->assertEquals('test_value', $result);
    }

    public function testGetReturnsDefaultForNonExistentKey()
    {
        $result = $this->cache->get('non_existent_key', 'default_value');

        $this->assertEquals('default_value', $result);
    }

    public function testGetReturnsNullByDefaultForNonExistentKey()
    {
        $result = $this->cache->get('non_existent_key');

        $this->assertNull($result);
    }

    public function testHasReturnsTrueForExistingKey()
    {
        $this->cache->set('test_key', 'test_value');

        $this->assertTrue($this->cache->has('test_key'));
    }

    public function testHasReturnsFalseForNonExistentKey()
    {
        $this->assertFalse($this->cache->has('non_existent_key'));
    }

    public function testDeleteRemovesValue()
    {
        $this->cache->set('test_key', 'test_value');
        $result = $this->cache->delete('test_key');

        $this->assertTrue($result);
        $this->assertFalse($this->cache->has('test_key'));
    }

    public function testDeleteReturnsTrueForNonExistentKey()
    {
        // Delete should succeed even if key doesn't exist
        $result = $this->cache->delete('non_existent_key');

        $this->assertTrue($result);
    }

    public function testClearRemovesAllValues()
    {
        $this->cache->set('key1', 'value1');
        $this->cache->set('key2', 'value2');
        $this->cache->set('key3', 'value3');

        $result = $this->cache->clear();

        $this->assertTrue($result);
        $this->assertFalse($this->cache->has('key1'));
        $this->assertFalse($this->cache->has('key2'));
        $this->assertFalse($this->cache->has('key3'));
    }

    public function testSetWithTTLExpiresAfterTime()
    {
        // Set with 1 second TTL
        $this->cache->set('test_key', 'test_value', 1);

        // Should exist immediately
        $this->assertTrue($this->cache->has('test_key'));

        // Wait for expiration
        sleep(2);

        // Should be expired
        $this->assertFalse($this->cache->has('test_key'));
        $this->assertNull($this->cache->get('test_key'));
    }

    public function testSetWithNullTTLNeverExpires()
    {
        $this->cache->set('test_key', 'test_value', null);

        // Should exist immediately
        $this->assertTrue($this->cache->has('test_key'));

        // Even after waiting, should still exist
        sleep(1);
        $this->assertTrue($this->cache->has('test_key'));
    }

    public function testGetMultipleReturnsArrayOfValues()
    {
        $this->cache->set('key1', 'value1');
        $this->cache->set('key2', 'value2');
        $this->cache->set('key3', 'value3');

        $result = $this->cache->getMultiple(['key1', 'key2', 'key3']);

        $this->assertIsArray($result);
        $this->assertEquals('value1', $result['key1']);
        $this->assertEquals('value2', $result['key2']);
        $this->assertEquals('value3', $result['key3']);
    }

    public function testGetMultipleReturnsDefaultForMissingKeys()
    {
        $this->cache->set('key1', 'value1');

        $result = $this->cache->getMultiple(['key1', 'key2', 'key3'], 'default');

        $this->assertEquals('value1', $result['key1']);
        $this->assertEquals('default', $result['key2']);
        $this->assertEquals('default', $result['key3']);
    }

    public function testSetMultipleStoresAllValues()
    {
        $values = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3'
        ];

        $result = $this->cache->setMultiple($values);

        $this->assertTrue($result);
        $this->assertTrue($this->cache->has('key1'));
        $this->assertTrue($this->cache->has('key2'));
        $this->assertTrue($this->cache->has('key3'));
    }

    public function testSetMultipleWithTTL()
    {
        $values = [
            'key1' => 'value1',
            'key2' => 'value2'
        ];

        $this->cache->setMultiple($values, 1);

        // Should exist immediately
        $this->assertTrue($this->cache->has('key1'));
        $this->assertTrue($this->cache->has('key2'));

        // Wait for expiration
        sleep(2);

        // Should be expired
        $this->assertFalse($this->cache->has('key1'));
        $this->assertFalse($this->cache->has('key2'));
    }

    public function testDeleteMultipleRemovesAllValues()
    {
        $this->cache->set('key1', 'value1');
        $this->cache->set('key2', 'value2');
        $this->cache->set('key3', 'value3');

        $result = $this->cache->deleteMultiple(['key1', 'key2']);

        $this->assertTrue($result);
        $this->assertFalse($this->cache->has('key1'));
        $this->assertFalse($this->cache->has('key2'));
        $this->assertTrue($this->cache->has('key3')); // key3 should still exist
    }

    public function testRememberReturnsExistingValue()
    {
        $this->cache->set('test_key', 'existing_value');

        $callbackCalled = false;
        $result = $this->cache->remember('test_key', 3600, function() use (&$callbackCalled) {
            $callbackCalled = true;
            return 'new_value';
        });

        // Should return existing value without calling callback
        $this->assertEquals('existing_value', $result);
        $this->assertFalse($callbackCalled);
    }

    public function testRememberExecutesCallbackForMissingKey()
    {
        $callbackCalled = false;
        $result = $this->cache->remember('test_key', 3600, function() use (&$callbackCalled) {
            $callbackCalled = true;
            return 'generated_value';
        });

        // Should execute callback and return generated value
        $this->assertEquals('generated_value', $result);
        $this->assertTrue($callbackCalled);

        // Value should be cached
        $this->assertEquals('generated_value', $this->cache->get('test_key'));
    }

    public function testRememberStoresGeneratedValueWithTTL()
    {
        $result = $this->cache->remember('test_key', 1, function() {
            return 'generated_value';
        });

        // Should store value
        $this->assertEquals('generated_value', $result);
        $this->assertTrue($this->cache->has('test_key'));

        // Wait for expiration
        sleep(2);

        // Should be expired
        $this->assertFalse($this->cache->has('test_key'));
    }

    public function testCacheHandlesVariousDataTypes()
    {
        $testData = [
            'string' => 'test_string',
            'integer' => 42,
            'float' => 3.14,
            'array' => ['a', 'b', 'c'],
            'object' => (object)['key' => 'value'],
            'boolean' => true,
            'null' => null
        ];

        foreach ($testData as $key => $value) {
            $this->cache->set($key, $value);
        }

        foreach ($testData as $key => $value) {
            $this->assertEquals($value, $this->cache->get($key));
        }
    }

    public function testCacheHandlesSpecialCharactersInKeys()
    {
        $keys = [
            'key-with-dashes',
            'key_with_underscores',
            'key.with.dots',
            'key:with:colons',
            'key/with/slashes'
        ];

        foreach ($keys as $key) {
            $this->cache->set($key, 'value_for_' . $key);
        }

        foreach ($keys as $key) {
            $this->assertEquals('value_for_' . $key, $this->cache->get($key));
        }
    }

    public function testGarbageCollectionRemovesExpiredEntries()
    {
        // Create some expired and non-expired entries
        $this->cache->set('expired1', 'value1', 1);
        $this->cache->set('expired2', 'value2', 1);
        $this->cache->set('valid', 'value3', 3600);

        // Wait for expiration
        sleep(2);

        // Run garbage collection
        $deletedCount = $this->cache->gc();

        // Should have deleted 2 expired entries
        $this->assertEquals(2, $deletedCount);
        $this->assertFalse($this->cache->has('expired1'));
        $this->assertFalse($this->cache->has('expired2'));
        $this->assertTrue($this->cache->has('valid'));
    }

    public function testGarbageCollectionReturnsZeroWhenNoExpiredEntries()
    {
        $this->cache->set('valid1', 'value1', 3600);
        $this->cache->set('valid2', 'value2', 3600);

        $deletedCount = $this->cache->gc();

        $this->assertEquals(0, $deletedCount);
        $this->assertTrue($this->cache->has('valid1'));
        $this->assertTrue($this->cache->has('valid2'));
    }

    public function testCacheDirectoryIsCreatedIfNotExists()
    {
        // Remove test directory
        $this->cleanupCacheDirectory();

        // Create new cache instance - should create directory
        $newCache = new FileCache($this->testCachePath, 3600);

        $this->assertTrue(is_dir($this->testCachePath));

        // Should be able to use cache
        $newCache->set('test', 'value');
        $this->assertEquals('value', $newCache->get('test'));
    }

    public function testCacheHandlesLargeValues()
    {
        // Create a large string (1MB)
        $largeValue = str_repeat('a', 1024 * 1024);

        $result = $this->cache->set('large_key', $largeValue);

        $this->assertTrue($result);
        $this->assertEquals($largeValue, $this->cache->get('large_key'));
    }

    public function testCacheKeyCollisionPrevention()
    {
        // These keys should generate different MD5 hashes
        $this->cache->set('abc', 'value1');
        $this->cache->set('ab c', 'value2');
        $this->cache->set('a bc', 'value3');

        $this->assertEquals('value1', $this->cache->get('abc'));
        $this->assertEquals('value2', $this->cache->get('ab c'));
        $this->assertEquals('value3', $this->cache->get('a bc'));
    }

    public function testGetDeletesExpiredEntryAutomatically()
    {
        $this->cache->set('test_key', 'test_value', 1);

        // Wait for expiration
        sleep(2);

        // Get should return default and delete expired entry
        $result = $this->cache->get('test_key', 'default');

        $this->assertEquals('default', $result);

        // Verify file was deleted
        $files = glob($this->testCachePath . '/*.cache');
        $this->assertEmpty($files);
    }

    public function testHasDeletesExpiredEntryAutomatically()
    {
        $this->cache->set('test_key', 'test_value', 1);

        // Wait for expiration with buffer time
        sleep(3);

        // Has should return false and delete expired entry
        $result = $this->cache->has('test_key');

        $this->assertFalse($result);

        // Verify file was deleted
        $files = glob($this->testCachePath . '/*.cache');
        $this->assertEmpty($files);
    }
}
