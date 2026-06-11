<?php

namespace App\Controllers\Admin;

use App\Models\Page;
use App\Models\Settings;
use App\Controllers\Admin\AdminController;
use App\Helpers\LogHelper;
use App\Helpers\RedirectHelper;
use App\Helpers\SessionHelper;

/**
 * Admin Settings Controller
 */
class SettingsController extends AdminController
{
    protected $page;
    protected $clsSettings;

    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->page = new Page();
        $this->clsSettings = new Settings();
    }

    public function indexAction()
    {
        // Elenco chiavi e valori di default
        $defaultSettings = [
            'homepage_mode' => 'latest',
            'homepage_page' => '',
            'site_title' => '',
            'site_description' => '',
            'site_language' => 'it',
            'site_timezone' => 'Europe/Rome',
            'posts_per_page' => 10,
            'comments_enabled' => 1,
            'meta_description' => '',
            'meta_keywords' => '',
        ];
        $settingsDB = $this->clsSettings->all();
        foreach ($settingsDB as $row) {
            $defaultSettings[$row['key']] = $row['value'];
        }
        $pages = $this->page->getAllForAdmin(); // Per selezione homepage
        $this->render('admin/settings', [
            'settings' => $defaultSettings,
            'pages' => $pages,
            'admin_url' => $this->settings['ADMIN_URL']
        ]);
    }

    public function saveAction()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            foreach ($_POST['settings'] as $key => $value) {
                $this->clsSettings->set($key, $value);
            }
            SessionHelper::setFlashMessage('Impostazioni salvate correttamente.', 'success');
        }
        RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/settings');
    }
}
