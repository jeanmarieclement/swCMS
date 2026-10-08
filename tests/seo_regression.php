<?php
/**
 * Standalone SEO regression checks. Run: php tests/seo_regression.php
 * Requires mbstring and pdo_sqlite. Uses only an in-memory database.
 */
require_once __DIR__ . '/../app/helpers/SeoHelper.php';
require_once __DIR__ . '/../app/core/HookSystem.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/models/Post.php';

use App\Helpers\SeoHelper;

$checks = 0;
function check($condition, $message)
{
    global $checks;
    if (!$condition) {
        throw new RuntimeException($message);
    }
    $checks++;
}

$cases = [
    ['<h2>Uno Strumento Dedicato</h2><p>Nel panorama...</p>', 'Uno Strumento Dedicato Nel panorama...'],
    ['<p>PHP&nbsp;&amp;&#160;Symfony</p>', 'PHP & Symfony'],
    ['<p>pro<strong>get</strong>to</p>', 'progetto'],
    ['<p>Ciao<br>mondo</p>', 'Ciao mondo'],
    ['<style>hidden</style><script>alert(1)</script><p>Visibile</p>', 'Visibile'],
    ['&lt;p&gt;Esempio&lt;/p&gt;', '<p>Esempio</p>'],
    ['<table><tr><td>Prima</td><td>Seconda</td></tr></table>', 'Prima Seconda'],
    [null, ''],
];
foreach ($cases as [$html, $expected]) {
    check(SeoHelper::metaDescription($html, 160) === $expected, 'HTML description: ' . $expected);
}
check(SeoHelper::metaDescription('Uno due tre', 7) === 'Uno due', 'Exact word boundary');
$cut = SeoHelper::metaDescription(str_repeat('parola ', 40), 50);
check($cut === implode(' ', array_fill(0, 7, 'parola')), 'Cut between words');
$cut = SeoHelper::metaDescription(str_repeat('à', 200), 150);
check(mb_check_encoding($cut, 'UTF-8') && mb_strlen($cut) === 150, 'UTF-8 long word');

$db = new PDO('sqlite::memory:', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$db->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, username TEXT, display_name TEXT)');
$db->exec('CREATE TABLE posts (id INTEGER PRIMARY KEY, title TEXT, slug TEXT UNIQUE, status TEXT, author_id INTEGER)');
$db->exec("INSERT INTO posts (id, title, slug, status) VALUES
    (1, 'Title different from URL', 'custom-permalink', 'published'),
    (2, 'Other post', 'occupied', 'published')");
$posts = new class($db) extends \App\Models\Post {
    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->table = 'posts';
        $this->hookSystem = \App\Core\HookSystem::getInstance();
    }
};
$posts->updatePost(1, ['title' => 'Title different from URL', 'slug' => 'custom-permalink']);
check($posts->getById(1)['slug'] === 'custom-permalink', 'Preserve owned custom slug');
$posts->updatePost(1, ['title' => 'Other post', 'slug' => 'occupied']);
check($posts->getById(1)['slug'] !== 'occupied', 'Do not take another post slug');
check($posts->getById(2)['slug'] === 'occupied', 'Keep other post unchanged');
$posts->updatePost(1, ['title' => 'New title', 'slug' => 'available-custom']);
check($posts->getById(1)['slug'] === 'available-custom', 'Allow available custom slug');
$posts->updatePost(1, ['title' => 'New title', 'slug' => '']);
check($posts->getById(1)['slug'] === 'new-title', 'Generate empty slug');
echo "$checks SEO regression checks passed.\n";
