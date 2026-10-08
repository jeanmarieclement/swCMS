<?php

namespace Tests\Unit\Models;

use App\Core\HookSystem;
use App\Models\Post;
use PHPUnit\Framework\TestCase;

class PostTimestampTest extends TestCase
{
    public function testUpdatingAPostRefreshesItsModificationDate(): void
    {
        $db = new \PDO('sqlite::memory:', null, null, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ]);
        $db->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, username TEXT, display_name TEXT)');
        $db->exec('CREATE TABLE posts (id INTEGER PRIMARY KEY, author_id INTEGER, title TEXT, slug TEXT, updated_at TEXT)');
        $db->exec("INSERT INTO posts (id, author_id, title, slug, updated_at) VALUES (1, 1, 'Post', 'post', '2000-01-01 00:00:00')");

        $post = new class($db) extends Post {
            public function __construct(\PDO $db)
            {
                $this->db = $db;
                $this->table = 'posts';
                $this->hookSystem = HookSystem::getInstance();
            }
        };

        $this->assertTrue($post->updatePost(1, ['title' => 'Post aggiornato', 'slug' => 'post']));

        $saved = $post->getById(1);
        $this->assertNotSame('2000-01-01 00:00:00', $saved['updated_at']);
        $this->assertMatchesRegularExpression('/^\\d{4}-\\d{2}-\\d{2} \\d{2}:\\d{2}:\\d{2}$/', $saved['updated_at']);
    }
}
