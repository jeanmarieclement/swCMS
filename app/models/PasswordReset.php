<?php

namespace App\Models;

use App\Core\Model;
use App\Core\HookSystem;

/**
 * PasswordReset Model
 *
 * Persists password reset tokens. The emailed link has to work from a browser
 * that never saw the session which requested the reset, so the token hash and
 * its expiry cannot live in that session.
 *
 * @package App\Models
 */
class PasswordReset extends Model
{
    protected $table = 'password_resets';

    /**
     * Constructor
     *
     * @param \PDO|null $pdo Optional connection, mainly for tests
     */
    public function __construct(\PDO $pdo = null)
    {
        if ($pdo === null) {
            parent::__construct();
            return;
        }

        // An injected connection skips the Database singleton entirely, which is
        // what lets the model be exercised against an in-memory SQLite database.
        $this->db = $pdo;
        $this->hookSystem = HookSystem::getInstance();
    }

    /**
     * Store a new reset token, superseding any token the user already has
     *
     * @param int $userId
     * @param string $tokenHash Hash of the token, never the token itself
     * @param string $expiresAt 'Y-m-d H:i:s'
     * @return bool
     */
    public function create($userId, $tokenHash, $expiresAt)
    {
        // The contract is that the new token replaces the old one. Two reset
        // requests racing each other would otherwise interleave their DELETE
        // and INSERT and leave the user with two live tokens.
        $ownsTransaction = !$this->db->inTransaction();

        if ($ownsTransaction) {
            $this->db->beginTransaction();
        }

        try {
            $this->deleteForUser($userId);

            $stmt = $this->db->prepare(
                "INSERT INTO {$this->table} (user_id, token_hash, expires_at)
                 VALUES (:user_id, :token_hash, :expires_at)"
            );

            $inserted = $stmt->execute([
                'user_id' => $userId,
                'token_hash' => $tokenHash,
                'expires_at' => $expiresAt
            ]);

            if (!$inserted) {
                if ($ownsTransaction) {
                    $this->db->rollBack();
                }

                return false;
            }

            if ($ownsTransaction) {
                $this->db->commit();
            }

            return true;
        } catch (\Exception $e) {
            if ($ownsTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Find the user's unused, unexpired token matching the given plaintext token
     *
     * @param int $userId
     * @param string $token Plaintext token from the reset link
     * @return array|null The row, or null when nothing matches
     */
    public function findValidByToken($userId, $token)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE user_id = :user_id AND used_at IS NULL AND expires_at > :now
             ORDER BY id DESC"
        );
        $stmt->execute([
            'user_id' => $userId,
            'now' => date('Y-m-d H:i:s')
        ]);

        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            if (password_verify($token, $row['token_hash'])) {
                return $row;
            }
        }

        return null;
    }

    /**
     * Claim a token, and report whether this caller is the one that got it
     *
     * The UPDATE is conditional on the token still being unused, so of two
     * requests replaying the same reset link exactly one sees a row change.
     * Callers must treat a false return as "this link is no longer valid" and
     * abandon whatever they were about to do with it.
     *
     * @param int $id
     * @return bool True only when this call is the one that consumed the token
     */
    public function consume($id)
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET used_at = :used_at
             WHERE id = :id AND used_at IS NULL"
        );

        $stmt->execute([
            'used_at' => date('Y-m-d H:i:s'),
            'id' => $id
        ]);

        return $stmt->rowCount() === 1;
    }

    /**
     * Delete every token belonging to a user
     *
     * @param int $userId
     * @return bool
     */
    public function deleteForUser($userId)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE user_id = :user_id");

        return $stmt->execute(['user_id' => $userId]);
    }

    /**
     * Delete tokens that are past their expiry
     *
     * @return bool
     */
    public function deleteExpired()
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE expires_at <= :now");

        return $stmt->execute(['now' => date('Y-m-d H:i:s')]);
    }
}
