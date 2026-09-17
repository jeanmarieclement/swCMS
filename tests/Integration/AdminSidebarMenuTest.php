<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use Smarty\Smarty;

class AdminSidebarMenuTest extends TestCase
{
    private $smarty;

    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('ROOT_PATH')) {
            define('ROOT_PATH', dirname(__DIR__, 2));
        }
        if (!defined('APP_PATH')) {
            define('APP_PATH', ROOT_PATH . '/app');
        }
        if (!defined('PUBLIC_PATH')) {
            define('PUBLIC_PATH', ROOT_PATH . '/public');
        }
        if (!defined('VIEWS_PATH')) {
            define('VIEWS_PATH', ROOT_PATH . '/app/views');
        }

        $compileDir = sys_get_temp_dir() . '/swcms_sidebar_test_compiled_' . md5(__DIR__);
        if (!is_dir($compileDir)) {
            mkdir($compileDir, 0777, true);
        }

        $this->smarty = new Smarty();
        $this->smarty->setTemplateDir(VIEWS_PATH);
        $this->smarty->setCompileDir($compileDir);
    }

    private function getSampleMenu(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Dashboard',
                'key' => 'dashboard',
                'position' => 1,
                'items' => [
                    [
                        'id' => 1,
                        'block_id' => 1,
                        'label' => 'Dashboard',
                        'url' => '/admin/dashboard',
                        'icon' => 'fas fa-tachometer-alt',
                        'children' => [],
                    ],
                ],
            ],
            [
                'id' => 2,
                'name' => 'Content',
                'key' => 'content',
                'position' => 2,
                'items' => [
                    [
                        'id' => 2,
                        'block_id' => 2,
                        'label' => 'Articles',
                        'url' => '/admin/articles',
                        'icon' => 'fas fa-newspaper',
                        'children' => [],
                    ],
                    [
                        'id' => 3,
                        'block_id' => 2,
                        'label' => 'Pages',
                        'url' => '/admin/pages',
                        'icon' => 'fas fa-file',
                        'children' => [],
                    ],
                ],
            ],
            [
                'id' => 7,
                'name' => 'Tools',
                'key' => 'tools',
                'position' => 40,
                'items' => [
                    [
                        'id' => 15,
                        'block_id' => 7,
                        'label' => 'Backup Manager',
                        'url' => '/admin/backup-manager',
                        'icon' => 'fas fa-puzzle-piece',
                        'children' => [],
                    ],
                ],
            ],
        ];
    }

    public function testSidebarMenuRendersScrollableContainer(): void
    {
        $this->smarty->assign('admin_menu', $this->getSampleMenu());
        $html = $this->smarty->fetch('admin/_menu_sidebar.tpl');

        $this->assertStringContainsString('id="sidebarMenu"', $html);
        $this->assertStringContainsString('class="sidebar-sticky pt-3"', $html);
        $this->assertStringContainsString('aria-label="Menu principale amministratore"', $html);
    }

    public function testSidebarMenuRendersCollapsibleGroupButtonsWithAriaAttributes(): void
    {
        $menu = $this->getSampleMenu();
        $this->smarty->assign('admin_menu', $menu);
        $html = $this->smarty->fetch('admin/_menu_sidebar.tpl');

        foreach ($menu as $block) {
            $groupId = 'sidebar-group-' . $block['id'];

            // Button toggle
            $this->assertStringContainsString('data-bs-toggle="collapse"', $html);
            $this->assertStringContainsString('data-bs-target="#' . $groupId . '"', $html);
            $this->assertStringContainsString('aria-controls="' . $groupId . '"', $html);
            $this->assertStringContainsString('aria-expanded="true"', $html);
            $this->assertStringContainsString('class="sidebar-group-toggle', $html);
            $this->assertStringContainsString($block['name'], $html);

            // Chevron icon
            $this->assertStringContainsString('fa-chevron-down sidebar-group-icon', $html);

            // Collapsible container
            $this->assertStringContainsString('id="' . $groupId . '"', $html);
            $this->assertStringContainsString('class="sidebar-group-collapse collapse show"', $html);
            $this->assertStringContainsString('data-group-id="' . $block['id'] . '"', $html);
        }
    }

    public function testSidebarMenuItemsRenderCorrectlyInsideGroups(): void
    {
        $this->smarty->assign('admin_menu', $this->getSampleMenu());
        $html = $this->smarty->fetch('admin/_menu_sidebar.tpl');

        $this->assertStringContainsString('/admin/dashboard', $html);
        $this->assertStringContainsString('Articles', $html);
        $this->assertStringContainsString('/admin/articles', $html);
        $this->assertStringContainsString('Pages', $html);
        $this->assertStringContainsString('/admin/pages', $html);
        $this->assertStringContainsString('Backup Manager', $html);
        $this->assertStringContainsString('/admin/backup-manager', $html);
    }
}
