<?php
require_once __DIR__ . '/../../app/core/Database/Migration.php';

use App\Core\Database\Migration;

/**
 * One live reset token per user, enforced by the database
 *
 * PasswordReset::create() promises that a new token supersedes the old one, but
 * a transaction cannot deliver that on its own: under READ COMMITTED two
 * concurrent requests both delete nothing, both insert, and the user ends up
 * with two working reset links. A unique index on user_id makes the promise
 * enforceable, and lets create() become a single upsert.
 *
 * @property \PDO $db
 */
class UniquePasswordResetPerUser extends Migration
{
    /**
     * @var \PDO
     */
    protected $db;

    public function __construct(\PDO $pdo = null)
    {
        parent::__construct($pdo);
    }

    /**
     * Collapse existing duplicates, then add the unique index
     */
    public function up()
    {
        // Keep the newest row per user: it is the one the last reset email
        // pointed at, so it is the only one the user could legitimately follow.
        $this->db->exec(
            "DELETE FROM password_resets
             WHERE id NOT IN (
                 SELECT keep_id FROM (
                     SELECT MAX(id) AS keep_id FROM password_resets GROUP BY user_id
                 ) AS newest
             )"
        );

        if ($this->isSqlite()) {
            $this->db->exec("DROP INDEX IF EXISTS idx_password_resets_user");
            $this->db->exec(
                "CREATE UNIQUE INDEX IF NOT EXISTS idx_password_resets_user
                 ON password_resets(user_id)"
            );

            return;
        }

        // MySQL cannot swap an index in place; dropping first keeps the table
        // with a single index on user_id rather than two overlapping ones. A
        // table that never had the index is fine, hence the tolerated failure.
        $this->dropIndexIfPresent();
        $this->db->exec(
            "ALTER TABLE password_resets
             ADD UNIQUE INDEX idx_password_resets_user (user_id)"
        );
    }

    /**
     * Put the plain index back
     */
    public function down()
    {
        if ($this->isSqlite()) {
            $this->db->exec("DROP INDEX IF EXISTS idx_password_resets_user");
            $this->db->exec(
                "CREATE INDEX IF NOT EXISTS idx_password_resets_user
                 ON password_resets(user_id)"
            );

            return;
        }

        $this->dropIndexIfPresent();
        $this->db->exec(
            "ALTER TABLE password_resets
             ADD INDEX idx_password_resets_user (user_id)"
        );
    }

    /**
     * Drop the user_id index on MySQL, tolerating its absence
     *
     * @return void
     */
    private function dropIndexIfPresent()
    {
        try {
            $this->db->exec("ALTER TABLE password_resets DROP INDEX idx_password_resets_user");
        } catch (\PDOException $e) {
            // 1091: can't DROP; check that column/key exists. Anything else is real.
            if ($e->getCode() !== '42000' && $e->errorInfo[1] !== 1091) {
                throw $e;
            }
        }
    }

    /**
     * @return bool
     */
    private function isSqlite()
    {
        return $this->db->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'sqlite';
    }
}
