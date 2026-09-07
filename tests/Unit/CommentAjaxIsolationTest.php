<?php

namespace Tests\Unit;

use App\Controllers\Frontend\CommentController;
use App\Models\Comments;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class CommentAjaxIsolationTest extends TestCase
{
    public static function requests(): array
    {
        return [
            [['post_id' => '5', 'page_id' => ''], 'article', 2],
            [['page_id' => '5', 'post_id' => ''], 'page', 1],
            [['page_id' => '5', 'post_id' => '5'], null, 0],
            [['page_id' => ['5']], null, 0],
        ];
    }

    /** @dataProvider requests */
    public function testAjaxUsesTheRequestedContentType(array $query, ?string $expected, int $total): void
    {
        require dirname(__DIR__) . '/Fixtures/comment_request_input.php';
        require_once dirname(__DIR__, 2) . '/database/migrations/2026_09_06_000001_separate_comment_content_references.php';
        $pdo = new \PDO('sqlite::memory:', null, null, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
        $pdo->exec('CREATE TABLE posts (id INTEGER PRIMARY KEY)');
        $pdo->exec('CREATE TABLE pages (id INTEGER PRIMARY KEY)');
        $pdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, username TEXT, display_name TEXT)');
        $pdo->exec('CREATE TABLE comments (id INTEGER PRIMARY KEY AUTOINCREMENT, post_id INTEGER NOT NULL,
            content TEXT, status TEXT, parent_id INTEGER, user_id INTEGER, created_at TEXT)');
        $pdo->exec('INSERT INTO posts VALUES (5)');
        $pdo->exec('INSERT INTO pages VALUES (5)');
        (new \SeparateCommentContentReferences($pdo))->up();
        $pdo->exec("INSERT INTO comments (post_id, page_id, content, status) VALUES
            (5, NULL, 'article', 'approved'), (5, NULL, 'article', 'approved'), (NULL, 5, 'page', 'approved')");
        $model = $this->getMockBuilder(Comments::class)->setConstructorArgs([$pdo])
            ->onlyMethods(['areCommentsEnabledForPost', 'areCommentsEnabledForPage'])->getMock();
        $model->method('areCommentsEnabledForPost')->willReturn(true);
        $model->method('areCommentsEnabledForPage')->willReturn(true);
        $class = new \ReflectionClass(CommentController::class);
        $controller = $class->newInstanceWithoutConstructor();
        $property = $class->getProperty('commentModel');
        $property->setAccessible(true);
        $property->setValue($controller, $model);
        $_GET = $query;
        ob_start();
        try {
            $controller->getCommentsAction();
            $response = json_decode(ob_get_contents(), true, 512, JSON_THROW_ON_ERROR);
        } finally {
            ob_end_clean();
        }
        if ($expected === null) {
            $this->assertArrayHasKey('error', $response);
        } else {
            $this->assertSame($total, $response['total']);
            $this->assertCount($total, $response['comments']);
            $this->assertSame([$expected], array_values(array_unique(array_column($response['comments'], 'content'))));
        }
    }
}
