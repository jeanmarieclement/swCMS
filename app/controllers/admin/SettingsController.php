<?php

namespace App\Controllers\Admin;

use App\Models\Page;
use App\Models\Settings;
use App\Controllers\Admin\AdminController;
use App\Helpers\LogHelper;
use App\Helpers\RedirectHelper;
use App\Helpers\RequestHelper;
use App\Helpers\SessionHelper;

/**
 * Admin Settings Controller
 */
class SettingsController extends AdminController
{
    /** Keys the settings form is allowed to write (matches admin/settings.tpl) */
    private const ALLOWED_KEYS = [
        'site_title', 'site_description', 'site_language', 'site_timezone',
        'SITE_NAME', 'SITE_URL', 'ADMIN_URL', 'THEME_ACTIVE',
        'homepage_mode', 'homepage_page',
        'meta_description', 'meta_keywords',
        'posts_per_page', 'comments_enabled', 'ALLOW_REGISTRATION',
        'MAIL_FROM', 'MAIL_FROM_NAME', 'SMTP_HOST', 'SMTP_PORT', 'SMTP_USER', 'SMTP_PASS',
        'SESSION_TIMEOUT', 'DEBUG_MODE', 'TIMEZONE', 'LANGUAGE',
    ];

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
        if (RequestHelper::isPost()) {
            $this->requireCsrf($this->settings['ADMIN_URL'] . '/settings', 'settings save');

            $submitted = RequestHelper::all('post')['settings'] ?? [];
            foreach ($submitted as $key => $value) {
                if (!in_array($key, self::ALLOWED_KEYS, true) || is_array($value)) {
                    LogHelper::warning('Settings save: skipped disallowed key "' . $key . '"');
                    continue;
                }
                $this->clsSettings->set($key, $value);
            }
            SessionHelper::setFlashMessage('Impostazioni salvate correttamente.', 'success');
        }
        RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/settings');
    }
}
