{* 404 Error page per il tema Verso Marte *}
{extends file="layout.tpl"}

{block name="title"}
    Houston, abbiamo un problema - 404 - {if isset($settings.site_title)}{$settings.site_title|escape}{else}Verso Marte{/if}
{/block}

{block name="description"}
    La pagina che stavi cercando si è persa nello spazio profondo. Esplora altre missioni dal nostro centro di controllo.
{/block}

{block name="content"}
    <div class="mars-404">
        {* 404 Hero Section *}
        <div class="error-hero">
            <div class="space-animation">
                <div class="lost-satellite">
                    <i class="fas fa-satellite"></i>
                </div>
                <div class="floating-debris">
                    <span class="debris"></span>
                    <span class="debris"></span>
                    <span class="debris"></span>
                </div>
            </div>
            
            <div class="error-content">
                <h1 class="error-code">
                    <span class="code-digit">4</span>
                    <i class="fas fa-globe-americas mars-planet"></i>
                    <span class="code-digit">4</span>
                </h1>
                
                <h2 class="error-title">
                    <i class="fas fa-exclamation-triangle"></i>
                    Houston, abbiamo un problema!
                </h2>
                
                <p class="error-message">
                    La pagina che stavi cercando si è persa nello spazio profondo.
                    Potrebbe essere finita nell'orbita di Marte o essere stata colpita da detriti spaziali.
                </p>
                
                <div class="mission-status-error">
                    <div class="status-line">
                        <i class="fas fa-satellite-dish"></i>
                        <span class="status-text">Status: Signal Lost</span>
                        <span class="status-indicator error"></span>
                    </div>
                    <div class="status-line">
                        <i class="fas fa-map-marked-alt"></i>
                        <span class="status-text">Location: Unknown</span>
                        <span class="status-indicator warning"></span>
                    </div>
                    <div class="status-line">
                        <i class="fas fa-clock"></i>
                        <span class="status-text">Last Contact: Just now</span>
                        <span class="status-indicator active"></span>
                    </div>
                </div>
            </div>
        </div>

        {* Navigation Options *}
        <div class="rescue-mission">
            <h3 class="rescue-title">
                <i class="fas fa-rocket"></i>
                Missioni di Salvataggio Disponibili
            </h3>
            
            <div class="rescue-options">
                <a href="{$settings.SITE_URL}/" class="rescue-btn primary">
                    <div class="btn-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="btn-content">
                        <h4>Base Terra</h4>
                        <p>Torna alla base di partenza</p>
                    </div>
                </a>
                
                <a href="{$settings.SITE_URL}/articles" class="rescue-btn">
                    <div class="btn-icon">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <div class="btn-content">
                        <h4>Archivio Missioni</h4>
                        <p>Esplora le missioni completate</p>
                    </div>
                </a>
                
                <a href="{$settings.SITE_URL}/contact" class="rescue-btn">
                    <div class="btn-icon">
                        <i class="fas fa-satellite"></i>
                    </div>
                    <div class="btn-content">
                        <h4>Mission Control</h4>
                        <p>Contatta il centro di controllo</p>
                    </div>
                </a>
                
                <button onclick="history.back()" class="rescue-btn">
                    <div class="btn-icon">
                        <i class="fas fa-undo"></i>
                    </div>
                    <div class="btn-content">
                        <h4>Rotta Precedente</h4>
                        <p>Torna all'ultima posizione</p>
                    </div>
                </button>
            </div>
        </div>

        {* Popular Content *}
        {if isset($popular_articles) && $popular_articles}
            <div class="popular-missions">
                <h3 class="section-title">
                    <i class="fas fa-star"></i>
                    Missioni Popolari
                </h3>
                <div class="popular-grid">
                    {foreach from=$popular_articles item=article}
                        <article class="popular-article">
                            <div class="article-icon">
                                <i class="fas fa-rocket"></i>
                            </div>
                            <div class="article-info">
                                <h4>
                                    <a href="{$settings.SITE_URL}/article/{$article.id}">
                                        {$article.title|escape|truncate:50}
                                    </a>
                                </h4>
                                <div class="article-meta">
                                    <span><i class="fas fa-calendar"></i> {$article.created_at|date_format:"%d/%m/%Y"}</span>
                                    {if isset($article.category)}
                                        <span><i class="fas fa-tag"></i> {$article.category.name|escape}</span>
                                    {/if}
                                </div>
                            </div>
                        </article>
                    {/foreach}
                </div>
            </div>
        {/if}

        {* Search Alternative *}
        <div class="space-search">
            <h3 class="search-title">
                <i class="fas fa-search"></i>
                Scansiona lo Spazio
            </h3>
            <p>Forse quello che cerchi è ancora là fuori, da qualche parte nella galassia...</p>
            
            <form class="search-form" action="{$settings.SITE_URL}/search" method="get">
                <div class="search-input-wrapper">
                    <input type="text" name="q" placeholder="Inserisci coordinate di ricerca..." 
                           class="search-input" value="{if isset($smarty.get.q)}{$smarty.get.q|escape}{/if}">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-satellite-dish"></i>
                        <span>Avvia Scansione</span>
                    </button>
                </div>
            </form>
        </div>

        {* Fun Mars Facts *}
        <div class="mars-curiosity">
            <h3 class="curiosity-title">
                <i class="fas fa-lightbulb"></i>
                Lo Sapevi Che...
            </h3>
            <div class="curiosity-facts">
                <div class="fact-item">
                    <i class="fas fa-globe"></i>
                    <p>Un giorno su Marte dura 24 ore e 37 minuti, quasi come sulla Terra!</p>
                </div>
                <div class="fact-item">
                    <i class="fas fa-mountain"></i>
                    <p>Il monte Olympus Mons su Marte è alto 21 km, tre volte l'Everest!</p>
                </div>
                <div class="fact-item">
                    <i class="fas fa-snowflake"></i>
                    <p>Su Marte nevica, ma è neve di anidride carbonica (ghiaccio secco)!</p>
                </div>
            </div>
        </div>
    </div>
{/block}

{block name="head_extra"}
    <style>
        /* Mars 404 Styles */
        .mars-404 {
            text-align: center;
        }

        /* Error Hero Section */
        .error-hero {
            background: var(--nebula-gradient);
            padding: 3rem 2rem;
            margin: -2rem -2rem 3rem -2rem;
            border-radius: 0 0 var(--border-radius) var(--border-radius);
            border-bottom: 3px solid var(--mars-red);
            position: relative;
            overflow: hidden;
        }

        /* Space Animation */
        .space-animation {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        .lost-satellite {
            position: absolute;
            top: 20%;
            right: 10%;
            animation: float 6s ease-in-out infinite;
        }

        .lost-satellite i {
            font-size: 3rem;
            color: var(--mars-orange);
            opacity: 0.7;
        }

        .floating-debris {
            position: absolute;
            width: 100%;
            height: 100%;
        }

        .debris {
            position: absolute;
            width: 4px;
            height: 4px;
            background: var(--starlight);
            border-radius: 50%;
            opacity: 0.8;
            animation: debris-float 8s linear infinite;
        }

        .debris:nth-child(1) {
            left: 20%;
            animation-delay: -2s;
            animation-duration: 10s;
        }

        .debris:nth-child(2) {
            left: 60%;
            animation-delay: -4s;
            animation-duration: 12s;
        }

        .debris:nth-child(3) {
            left: 80%;
            animation-delay: -6s;
            animation-duration: 8s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            25% { transform: translateY(-20px) rotate(90deg); }
            50% { transform: translateY(0px) rotate(180deg); }
            75% { transform: translateY(-10px) rotate(270deg); }
        }

        @keyframes debris-float {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100px) rotate(360deg);
                opacity: 0;
            }
        }

        /* Error Content */
        .error-content {
            position: relative;
            z-index: 2;
        }

        .error-code {
            font-family: var(--font-heading);
            font-size: 8rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        .code-digit {
            color: var(--mars-red);
            text-shadow: 0 0 20px var(--mars-red);
        }

        .mars-planet {
            color: var(--mars-orange);
            font-size: 6rem;
            animation: mars-orbit 10s linear infinite;
        }

        @keyframes mars-orbit {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .error-title {
            font-family: var(--font-heading);
            color: var(--gold-accent);
            font-size: 2rem;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.7);
        }

        .error-title i {
            color: var(--mars-orange);
            margin-right: 0.5rem;
        }

        .error-message {
            color: var(--starlight);
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto 2rem;
            line-height: 1.6;
        }

        /* Mission Status */
        .mission-status-error {
            background: rgba(26, 26, 46, 0.7);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            max-width: 500px;
            margin: 0 auto;
            backdrop-filter: blur(10px);
        }

        .status-line {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            padding: 0.5rem;
        }

        .status-line:last-child {
            margin-bottom: 0;
        }

        .status-text {
            display: flex;
            align-items: center;
            color: var(--starlight);
        }

        .status-text i {
            margin-right: 0.5rem;
            color: var(--mars-orange);
        }

        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .status-indicator.error { background: #ff4757; }
        .status-indicator.warning { background: #ffa502; }
        .status-indicator.active { background: #2ed573; }

        /* Rescue Mission */
        .rescue-mission {
            margin: 3rem 0;
        }

        .rescue-title {
            font-family: var(--font-heading);
            color: var(--mars-orange);
            font-size: 1.8rem;
            margin-bottom: 2rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .rescue-title i {
            color: var(--gold-accent);
            margin-right: 0.5rem;
        }

        .rescue-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .rescue-btn {
            display: flex;
            align-items: center;
            background: rgba(26, 26, 46, 0.7);
            border: 2px solid rgba(205, 92, 92, 0.3);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            color: var(--starlight);
            text-decoration: none;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        }

        .rescue-btn:hover {
            border-color: var(--mars-orange);
            background: rgba(26, 26, 46, 0.9);
            transform: translateY(-3px);
            box-shadow: var(--shadow-mars);
            color: var(--starlight);
        }

        .rescue-btn.primary {
            border-color: var(--gold-accent);
            background: rgba(30, 58, 138, 0.3);
        }

        .rescue-btn.primary:hover {
            border-color: var(--gold-accent);
            background: rgba(30, 58, 138, 0.5);
        }

        .btn-icon {
            background: var(--mars-gradient);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .btn-icon i {
            color: white;
            font-size: 1.5rem;
        }

        .btn-content h4 {
            color: var(--gold-accent);
            margin-bottom: 0.25rem;
            font-family: var(--font-heading);
        }

        .btn-content p {
            color: var(--starlight);
            opacity: 0.8;
            font-size: 0.9rem;
            margin: 0;
        }

        /* Popular Missions */
        .popular-missions,
        .space-search,
        .mars-curiosity {
            margin: 3rem 0;
            background: rgba(26, 26, 46, 0.7);
            border-radius: var(--border-radius);
            padding: 2rem;
            border: 1px solid rgba(205, 92, 92, 0.3);
        }

        .section-title,
        .search-title,
        .curiosity-title {
            font-family: var(--font-heading);
            color: var(--mars-orange);
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .section-title i,
        .search-title i,
        .curiosity-title i {
            color: var(--gold-accent);
            margin-right: 0.5rem;
        }

        .popular-grid {
            display: grid;
            gap: 1rem;
        }

        .popular-article {
            display: flex;
            align-items: center;
            background: rgba(30, 58, 138, 0.2);
            border-radius: var(--border-radius);
            padding: 1rem;
            border: 1px solid rgba(30, 58, 138, 0.3);
            transition: all 0.3s ease;
        }

        .popular-article:hover {
            border-color: var(--mars-orange);
            transform: translateX(5px);
        }

        .article-icon {
            background: var(--mars-gradient);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .article-icon i {
            color: white;
        }

        .article-info h4 {
            margin-bottom: 0.25rem;
        }

        .article-info h4 a {
            color: var(--gold-accent);
            text-decoration: none;
        }

        .article-meta {
            display: flex;
            gap: 1rem;
            font-size: 0.8rem;
            color: var(--mars-dust);
        }

        .article-meta i {
            margin-right: 0.25rem;
        }

        /* Search Form */
        .space-search p {
            color: var(--starlight);
            opacity: 0.8;
            margin-bottom: 2rem;
        }

        .search-input-wrapper {
            display: flex;
            max-width: 500px;
            margin: 0 auto;
            background: rgba(26, 26, 46, 0.8);
            border-radius: var(--border-radius);
            overflow: hidden;
            border: 2px solid rgba(205, 92, 92, 0.3);
        }

        .search-input {
            flex-grow: 1;
            padding: 1rem;
            background: transparent;
            border: none;
            color: var(--starlight);
            font-family: var(--font-body);
        }

        .search-input:focus {
            outline: none;
        }

        .search-input::placeholder {
            color: var(--mars-dust);
            opacity: 0.7;
        }

        .search-btn {
            background: var(--mars-gradient);
            border: none;
            padding: 1rem 1.5rem;
            color: white;
            font-family: var(--font-heading);
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .search-btn:hover {
            background: var(--gold-accent);
            color: var(--deep-space);
        }

        .search-btn i {
            margin-right: 0.5rem;
        }

        /* Mars Curiosity */
        .curiosity-facts {
            display: grid;
            gap: 1rem;
        }

        .fact-item {
            display: flex;
            align-items: flex-start;
            background: rgba(30, 58, 138, 0.2);
            padding: 1rem;
            border-radius: var(--border-radius);
            border-left: 4px solid var(--mars-red);
        }

        .fact-item i {
            color: var(--mars-orange);
            margin-right: 1rem;
            margin-top: 0.25rem;
            flex-shrink: 0;
        }

        .fact-item p {
            color: var(--starlight);
            margin: 0;
            line-height: 1.5;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .error-code {
                font-size: 4rem;
            }
            
            .mars-planet {
                font-size: 3rem;
            }
            
            .error-title {
                font-size: 1.5rem;
            }
            
            .error-message {
                font-size: 1rem;
            }
            
            .rescue-options {
                grid-template-columns: 1fr;
            }
            
            .rescue-btn {
                flex-direction: column;
                text-align: center;
            }
            
            .btn-icon {
                margin: 0 0 1rem 0;
            }
            
            .search-input-wrapper {
                flex-direction: column;
            }
            
            .search-btn {
                border-radius: 0 0 var(--border-radius) var(--border-radius);
            }
        }
    </style>
{/block}