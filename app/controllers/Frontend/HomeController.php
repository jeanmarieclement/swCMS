<?php

namespace App\Controllers\Frontend;

use App\Models\Page;
use App\Models\Settings;
use App\Models\Post;
use App\Controllers\Frontend\BaseController;

/**
 * HomeController gestisce la homepage pubblica
 */
class HomeController extends BaseController {
   

    protected $pageModel;
    protected $settingsModel;
    protected $postModel;
    
    public function __construct($params = []) {
        parent::__construct($params);
        $this->pageModel = new Page();
        $this->settingsModel = new Settings();
        $this->postModel = new Post();
    }

    /**
     * Displays the home page with the latest posts.
     *
     * @return void
     */
    public function indexAction() {
        // Carica le impostazioni del sito
       

        $homepageMode = $this->settings['homepage_mode'] ?? 'latest';
        $homepagePageId = $this->settings['homepage_page'] ?? '';

        // Esempio di menu principale statico (da sostituire con menu dinamico se presente)
        $mainMenu = [
            ['url' => '/', 'label' => 'Home'],
            ['url' => '/articoli', 'label' => 'Articoli'],
            ['url' => '/contatti', 'label' => 'Contatti']
        ];

        if ($homepageMode == 'page' && !empty($homepagePageId)) {
            // Mostra una pagina specifica come homepage
            $page = $this->pageModel->findById($homepagePageId);
            if ($page && $page['status'] === 'published') {
                // Renderizza la homepage con la pagina selezionata
                $this->render('home', [
                    'settings' => $this->settings,
                    'homepage_page' => $page,
                    'latest_posts' => null,
                    'main_menu' => $mainMenu,
                    
                ]);
                return;
            }
            // Se la pagina non è trovata o non è pubblicata, fallback
        }

        // Fallback: mostra ultimi articoli
        $latestArticles = $this->postModel->getLatest(6);
      
        $this->render('home', [
            'settings' => $this->settings,
            'site_title' => $this->settings['site_title'] ?? 'swCMS',
            'main_menu' => $mainMenu,
            'latest_articles' => $latestArticles,
            'homepage_page' => null, // Set to null when showing articles, not the page ID
        ]);
    }
    
    
    /**
     * Displays the About Us page.
     *
     * @return void
     */
    public function aboutAction() {
        $this->render('about', [
            'title' => 'About Us'
        ]);
    }
    
    
    /**
     * Displays the Contact Us page.
     *
     * @return void
     */
    public function contactAction() {
        $this->render('contact', [
            'title' => 'Contact Us'
        ]);
    }
}
