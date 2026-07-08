<?php
require_once __DIR__ . '/../../app/core/Database/Migration.php';

use App\Core\Database\Migration;

class SeedRoles extends Migration
{
    public function __construct(\PDO $pdo = null)
    {
        parent::__construct($pdo);
    }

    public function up()
    {
        // Remove any duplicates from earlier double-seeding, then enforce
        // uniqueness so INSERT OR IGNORE actually protects against re-runs
        $this->db->exec("DELETE FROM roles WHERE id NOT IN (SELECT MIN(id) FROM roles GROUP BY name)");
        $this->db->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_roles_name ON roles(name)");

        $roles = [
            ['admin',      'Administrator with full access',           4],
            ['editor',     'Editor with content management access',    3],
            ['author',     'Author with limited content creation access', 2],
            ['subscriber', 'Subscriber with read-only access',         1],
        ];

        $stmt = $this->db->prepare(
            "INSERT OR IGNORE INTO roles (name, description, level) VALUES (?, ?, ?)"
        );

        foreach ($roles as [$name, $desc, $level]) {
            $stmt->execute([$name, $desc, $level]);
        }
    }

    public function down()
    {
        $this->db->exec("DELETE FROM roles WHERE name IN ('admin','editor','author','subscriber')");
    }
}
