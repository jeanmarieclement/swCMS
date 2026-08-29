<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\MediaModel;

/**
 * MediaModel Test
 * Covers the "not found" contract of getById() and the featured_image cleanup
 * deleteMedia() performs, both against an in-memory SQLite database.
 *
 * @package Tests\Unit\Models
 */
class MediaModelTest extends TestCase
{
    /** @var \PDO */
    private $pdo;

    /** @var MediaModel */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec("CREATE TABLE media (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            filename VARCHAR(255) NOT NULL,
            filepath VARCHAR(255) NOT NULL,
            filetype VARCHAR(100) NOT NULL DEFAULT 'image/jpeg',
            filesize INTEGER NOT NULL DEFAULT 0,
            title VARCHAR(255) DEFAULT NULL,
            description TEXT DEFAULT NULL,
            alt_text VARCHAR(255) DEFAULT NULL,
            user_id INTEGER NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT NULL
        )");
        $this->pdo->exec("CREATE TABLE posts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title VARCHAR(255) NOT NULL,
            featured_image VARCHAR(255) DEFAULT NULL
        )");

        $this->model = new MediaModel($this->pdo);
    }

    private function insertMedia(string $filepath = '/2026/08/', string $filename = 'photo.jpg'): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO media (filename, filepath, filetype) VALUES (:filename, :filepath, 'image/jpeg')"
        );
        $stmt->execute(['filename' => $filename, 'filepath' => $filepath]);

        return (int) $this->pdo->lastInsertId();
    }

    private function insertPost(string $title, $featuredImage): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO posts (title, featured_image) VALUES (:title, :featured_image)"
        );
        $stmt->execute(['title' => $title, 'featured_image' => $featuredImage]);

        return (int) $this->pdo->lastInsertId();
    }

    private function featuredImageOf(int $postId)
    {
        $stmt = $this->pdo->prepare("SELECT featured_image FROM posts WHERE id = :id");
        $stmt->execute(['id' => $postId]);

        return $stmt->fetchColumn();
    }

    public function testGetByIdReturnsNullWhenNoRowMatches()
    {
        $this->assertNull($this->model->getById(999999));
    }

    public function testGetByIdReturnsTheRow()
    {
        $id = $this->insertMedia();

        $media = $this->model->getById($id);

        $this->assertIsObject($media);
        $this->assertEquals('photo.jpg', $media->filename);
    }

    public function testDeleteMediaClearsPostsReferencingTheFullSizeUrl()
    {
        $id = $this->insertMedia();
        $postId = $this->insertPost('With image', '/uploads/media/2026/08/photo.jpg');

        $this->model->deleteMedia($id);

        $this->assertNull($this->featuredImageOf($postId));
    }

    public function testDeleteMediaClearsPostsReferencingTheThumbnailUrl()
    {
        // The media picker stores the thumbnail URL for images
        $id = $this->insertMedia();
        $postId = $this->insertPost('With thumb', '/uploads/media/2026/08/thumbs/photo.jpg');

        $this->model->deleteMedia($id);

        $this->assertNull($this->featuredImageOf($postId));
    }

    public function testDeleteMediaLeavesOtherPostsAlone()
    {
        $id = $this->insertMedia();
        $otherPost = $this->insertPost('Other image', '/uploads/media/2026/08/other.jpg');

        $this->model->deleteMedia($id);

        $this->assertEquals('/uploads/media/2026/08/other.jpg', $this->featuredImageOf($otherPost));
    }

    public function testDeleteMediaRemovesTheRow()
    {
        $id = $this->insertMedia();

        $this->model->deleteMedia($id);

        $this->assertNull($this->model->getById($id));
    }

    public function testDeleteMediaThrowsWhenTheMediaIsMissing()
    {
        $this->expectException(\Exception::class);

        $this->model->deleteMedia(999999);
    }
}
