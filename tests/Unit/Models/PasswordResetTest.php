<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\PasswordReset;

// Composer autoloads app/ through a classmap, so a model added after the last
// `composer dump-autoload` is invisible to the test run. The application itself
// resolves it through App\Core\Autoloader's PSR-4 lookup.
require_once dirname(__DIR__, 3) . '/app/models/PasswordReset.php';

/**
 * PasswordReset Test
 * Reset tokens must survive the session that requested them, so they live in
 * the database. These tests run against an in-memory SQLite database.
 *
 * @package Tests\Unit\Models
 */
class PasswordResetTest extends TestCase
{
    /** @var \PDO */
    private $pdo;

    /** @var PasswordReset */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        $this->pdo->exec("CREATE TABLE password_resets (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            token_hash VARCHAR(255) NOT NULL,
            expires_at TIMESTAMP NOT NULL,
            used_at TIMESTAMP DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        )");

        $this->model = new PasswordReset($this->pdo);
    }

    private function futureDate(): string
    {
        return date('Y-m-d H:i:s', time() + 3600);
    }

    private function pastDate(): string
    {
        return date('Y-m-d H:i:s', time() - 60);
    }

    public function testCreateStoresTheTokenHash()
    {
        $hash = password_hash('token-abc', PASSWORD_BCRYPT);

        $this->assertTrue($this->model->create(7, $hash, $this->futureDate()));

        $rows = $this->pdo->query("SELECT * FROM password_resets")->fetchAll();
        $this->assertCount(1, $rows);
        $this->assertEquals(7, $rows[0]['user_id']);
        $this->assertEquals($hash, $rows[0]['token_hash']);
        $this->assertNull($rows[0]['used_at']);
    }

    public function testCreateInvalidatesPreviousTokensForTheSameUser()
    {
        $this->model->create(7, password_hash('first', PASSWORD_BCRYPT), $this->futureDate());
        $this->model->create(7, password_hash('second', PASSWORD_BCRYPT), $this->futureDate());

        $rows = $this->pdo->query("SELECT * FROM password_resets WHERE user_id = 7")->fetchAll();
        $this->assertCount(1, $rows, 'Requesting a new reset must supersede the previous token');
        $this->assertTrue(password_verify('second', $rows[0]['token_hash']));
    }

    public function testCreateLeavesOtherUsersTokensAlone()
    {
        $this->model->create(7, password_hash('seven', PASSWORD_BCRYPT), $this->futureDate());
        $this->model->create(8, password_hash('eight', PASSWORD_BCRYPT), $this->futureDate());

        $this->assertNotNull($this->model->findValidByToken(7, 'seven'));
        $this->assertNotNull($this->model->findValidByToken(8, 'eight'));
    }

    public function testFindValidByTokenMatchesTheToken()
    {
        $this->model->create(7, password_hash('token-abc', PASSWORD_BCRYPT), $this->futureDate());

        $row = $this->model->findValidByToken(7, 'token-abc');

        $this->assertIsArray($row);
        $this->assertEquals(7, $row['user_id']);
    }

    public function testFindValidByTokenRejectsAWrongToken()
    {
        $this->model->create(7, password_hash('token-abc', PASSWORD_BCRYPT), $this->futureDate());

        $this->assertNull($this->model->findValidByToken(7, 'token-xyz'));
    }

    public function testFindValidByTokenRejectsAnotherUsersToken()
    {
        $this->model->create(7, password_hash('token-abc', PASSWORD_BCRYPT), $this->futureDate());

        $this->assertNull($this->model->findValidByToken(8, 'token-abc'));
    }

    public function testFindValidByTokenRejectsAnExpiredToken()
    {
        $this->model->create(7, password_hash('token-abc', PASSWORD_BCRYPT), $this->pastDate());

        $this->assertNull($this->model->findValidByToken(7, 'token-abc'));
    }

    public function testFindValidByTokenRejectsAnAlreadyUsedToken()
    {
        $this->model->create(7, password_hash('token-abc', PASSWORD_BCRYPT), $this->futureDate());
        $row = $this->model->findValidByToken(7, 'token-abc');
        $this->model->consume($row['id']);

        $this->assertNull($this->model->findValidByToken(7, 'token-abc'));
    }

    public function testConsumeReportsSuccessOnlyForTheCallerThatClaimsTheToken()
    {
        // Two requests replaying the same reset link both get past
        // findValidByToken(); only one of them may go on to set a password.
        $this->model->create(7, password_hash('token-abc', PASSWORD_BCRYPT), $this->futureDate());
        $row = $this->model->findValidByToken(7, 'token-abc');

        $this->assertTrue($this->model->consume($row['id']));
        $this->assertFalse($this->model->consume($row['id']));
    }

    public function testConsumeReportsFailureForATokenThatDoesNotExist()
    {
        $this->assertFalse($this->model->consume(999));
    }

    public function testConsumeLeavesOtherTokensAlone()
    {
        $this->model->create(7, password_hash('token-abc', PASSWORD_BCRYPT), $this->futureDate());
        $this->model->create(8, password_hash('token-def', PASSWORD_BCRYPT), $this->futureDate());

        $mine = $this->model->findValidByToken(7, 'token-abc');
        $this->model->consume($mine['id']);

        $this->assertIsArray($this->model->findValidByToken(8, 'token-def'));
    }

    public function testCreateReplacesTheOldTokenAtomically()
    {
        $this->model->create(7, password_hash('old', PASSWORD_BCRYPT), $this->futureDate());
        $this->model->create(7, password_hash('new', PASSWORD_BCRYPT), $this->futureDate());

        $rows = $this->pdo
            ->query("SELECT id FROM password_resets WHERE user_id = 7")
            ->fetchAll();

        $this->assertCount(1, $rows, 'A new token must supersede the previous one');
        $this->assertNull($this->model->findValidByToken(7, 'old'));
        $this->assertIsArray($this->model->findValidByToken(7, 'new'));
    }

    public function testCreateLeavesNoOpenTransactionBehind()
    {
        // create() manages its own transaction; a caller that later starts one
        // must not trip over a connection that is still inside it.
        $this->model->create(7, password_hash('token-abc', PASSWORD_BCRYPT), $this->futureDate());

        $this->assertFalse($this->pdo->inTransaction());
    }

    public function testCreateJoinsAnAlreadyOpenTransactionInsteadOfNesting()
    {
        // Rolling the caller's transaction back must undo the token too: create()
        // is not allowed to commit out from under it.
        $this->pdo->beginTransaction();
        $this->model->create(7, password_hash('token-abc', PASSWORD_BCRYPT), $this->futureDate());
        $this->assertTrue($this->pdo->inTransaction(), 'create() must not commit the caller\'s transaction');
        $this->pdo->rollBack();

        $this->assertNull($this->model->findValidByToken(7, 'token-abc'));
    }

    public function testTokenSurvivesADifferentSession()
    {
        // The whole point of #7: a token created in one request must still be
        // usable from a browser that never saw the session that created it.
        $this->model->create(7, password_hash('token-abc', PASSWORD_BCRYPT), $this->futureDate());

        $_SESSION = [];
        $otherModel = new PasswordReset($this->pdo);

        $this->assertIsArray($otherModel->findValidByToken(7, 'token-abc'));
    }

    public function testDeleteExpiredRemovesOnlyStaleRows()
    {
        $this->model->create(7, password_hash('fresh', PASSWORD_BCRYPT), $this->futureDate());
        $this->pdo->exec("INSERT INTO password_resets (user_id, token_hash, expires_at)
            VALUES (8, 'stale', '" . $this->pastDate() . "')");

        $this->model->deleteExpired();

        $rows = $this->pdo->query("SELECT user_id FROM password_resets")->fetchAll();
        $this->assertCount(1, $rows);
        $this->assertEquals(7, $rows[0]['user_id']);
    }
}
