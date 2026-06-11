<?php
require_once __DIR__ . '/../../app/core/Database/Migration.php';

use App\Core\Database\Migration;

class CreateMenusTable extends Migration {
    
    public function up() {
        $pdo = $this->db;

        if (defined('DB_DRIVER') && DB_DRIVER === 'sqlite') {
            $sql = "CREATE TABLE IF NOT EXISTS menus (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title VARCHAR(255) NOT NULL,
                url VARCHAR(500) NOT NULL,
                location VARCHAR(50) NOT NULL DEFAULT 'header',
                position INTEGER NOT NULL DEFAULT 0,
                parent_id INTEGER NULL,
                active INTEGER NOT NULL DEFAULT 1,
                target VARCHAR(20) NOT NULL DEFAULT '_self',
                css_class VARCHAR(255) DEFAULT '',
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (parent_id) REFERENCES menus(id) ON DELETE CASCADE
            )";
        } else {
            $sql = "CREATE TABLE IF NOT EXISTS menus (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                url VARCHAR(500) NOT NULL,
                location VARCHAR(50) NOT NULL DEFAULT 'header',
                position INT NOT NULL DEFAULT 0,
                parent_id INT NULL,
                active TINYINT(1) NOT NULL DEFAULT 1,
                target VARCHAR(20) NOT NULL DEFAULT '_self',
                css_class VARCHAR(255) DEFAULT '',
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (parent_id) REFERENCES menus(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        }

        $pdo->exec($sql);

        // Indici per migliorare le performance
        $pdo->exec("CREATE INDEX idx_menus_location ON menus(location)");
        $pdo->exec("CREATE INDEX idx_menus_active ON menus(active)");
        $pdo->exec("CREATE INDEX idx_menus_parent_id ON menus(parent_id)");
        $pdo->exec("CREATE INDEX idx_menus_position ON menus(position)");

        // Inserisci alcuni menu di esempio
        $defaultMenus = [
            [
                'title' => 'Home',
                'url' => '/',
                'location' => 'header',
                'position' => 1,
                'parent_id' => null,
                'active' => 1,
                'target' => '_self',
                'css_class' => 'menu-home'
            ],
            [
                'title' => 'Chi Siamo',
                'url' => '/about',
                'location' => 'header',
                'position' => 2,
                'parent_id' => null,
                'active' => 1,
                'target' => '_self',
                'css_class' => ''
            ],
            [
                'title' => 'Servizi',
                'url' => '/services',
                'location' => 'header',
                'position' => 3,
                'parent_id' => null,
                'active' => 1,
                'target' => '_self',
                'css_class' => ''
            ],
            [
                'title' => 'Contatti',
                'url' => '/contact',
                'location' => 'header',
                'position' => 4,
                'parent_id' => null,
                'active' => 1,
                'target' => '_self',
                'css_class' => ''
            ]
        ];

        $ts = (defined('DB_DRIVER') && DB_DRIVER === 'sqlite') ? 'CURRENT_TIMESTAMP' : 'NOW()';
        $stmt = $pdo->prepare("INSERT INTO menus (title, url, location, position, parent_id, active, target, css_class, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, $ts, $ts)");
        
        foreach ($defaultMenus as $menu) {
            $stmt->execute([
                $menu['title'],
                $menu['url'],
                $menu['location'],
                $menu['position'],
                $menu['parent_id'],
                $menu['active'],
                $menu['target'],
                $menu['css_class']
            ]);
        }
        
        echo "Tabella 'menus' creata con successo con menu di esempio.\n";
    }
    
    public function down() {
        $pdo = $this->db;
        $pdo->exec("DROP TABLE IF EXISTS menus");
        echo "Tabella 'menus' eliminata.\n";
    }
}