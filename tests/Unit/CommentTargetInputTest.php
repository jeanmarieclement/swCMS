<?php

namespace Tests\Unit;

use App\Controllers\Frontend\CommentController;
use PHPUnit\Framework\TestCase;

class CommentTargetInputTest extends TestCase
{
    public static function targets(): array
    {
        return [
            'article form with blank page field' => ['5', '', ['post_id' => 5]],
            'page form with blank article field' => ['', '5', ['page_id' => 5]],
            'article AJAX request' => ['5', null, ['post_id' => 5]],
            'page AJAX request' => [null, '5', ['page_id' => 5]],
            'both targets' => ['5', '5', null],
            'missing targets' => [null, null, null],
            'empty form' => ['', '', null],
            'negative article' => ['-5', null, null],
            'zero page' => [null, '0', null],
            'array article' => [['5'], null, null],
            'malformed other field' => ['5', 'invalid', null],
            'filter rejected an array' => [false, '5', null],
        ];
    }

    /** @dataProvider targets */
    public function testOnlyOnePositiveTargetIsAccepted($post, $page, $expected): void
    {
        $class = new \ReflectionClass(CommentController::class);
        $controller = $class->newInstanceWithoutConstructor();
        $method = $class->getMethod('parseContentTarget');
        $method->setAccessible(true);
        $this->assertSame($expected, $method->invoke($controller, $post, $page));
    }
}
