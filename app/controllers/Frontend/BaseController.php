<?php

namespace App\Controllers\Frontend;

use App\Core\Controller;
use App\Models\Menu;

class BaseController extends Controller
{
    protected $menuModel;

    /**
     * BaseController constructor.
     *
     * @param array $params Optional parameters
     */
    public function __construct($params = [])
    {
        parent::__construct($params);

        // Carica il modello Menu
        $this->menuModel = new Menu();
    }

    /**
     * Override del metodo render per aggiungere i menu globalmente
     */
    protected function render($template, $data = [])
    {
        // Carica i menu per posizione
        $headerMenu = $this->menuModel->getMenuHierarchy('header');
        $footerMenu = $this->menuModel->getMenuHierarchy('footer');

        // Aggiungi i menu ai dati del template
        $data['header_menu'] = $headerMenu;
        $data['footer_menu'] = $footerMenu;

        // Determina la pagina attiva per evidenziare i menu
        $currentUrl = $_SERVER['REQUEST_URI'] ?? '/';
        $data = $this->markActiveMenuItems($data, $currentUrl);

        // Chiama il metodo render del parent
        parent::render($template, $data);
    }

    /**
     * Marca i menu item attivi in base all'URL corrente
     */
    private function markActiveMenuItems($data, $currentUrl)
    {
        // Funzione ricorsiva per marcare gli item attivi
        $markActive = function (&$menuItems, $url) use (&$markActive) {
            foreach ($menuItems as &$item) {
                // Confronta URL (rimuovi parametri GET)
                $itemUrl = parse_url($item['url'], PHP_URL_PATH) ?: $item['url'];
                $currentPath = parse_url($url, PHP_URL_PATH) ?: $url;

                $item['active_page'] = ($itemUrl === $currentPath);

                // Controlla anche i figli
                if (isset($item['children']) && !empty($item['children'])) {
                    $markActive($item['children'], $url);

                    // Se un figlio è attivo, marca anche il genitore come attivo
                    foreach ($item['children'] as $child) {
                        if ($child['active_page']) {
                            $item['active_page'] = true;
                            break;
                        }
                    }
                }
            }
        };

        if (isset($data['header_menu'])) {
            $markActive($data['header_menu'], $currentUrl);
        }

        if (isset($data['footer_menu'])) {
            $markActive($data['footer_menu'], $currentUrl);
        }

        return $data;
    }

    /**
     * Default action
     */
    public function indexAction()
    {
        // default action
    }
}
