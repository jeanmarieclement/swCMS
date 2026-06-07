{* Home page per il tema Verso Marte *}
{extends file="layout.tpl"}

{block name="title"}
    {if isset($settings.site_title)}{$settings.site_title|escape} - Base Terra{else}Verso Marte - Il Viaggio Inizia{/if}
{/block}

{block name="description"}
    Scopri il viaggio verso Marte: tecnologia, scienza e avventura spaziale. Il pianeta rosso ci aspetta.
{/block}

{block name="keywords"}
    Marte, spazio, esplorazione spaziale, pianeta rosso, NASA, SpaceX, missione Mars, astronauti
{/block}

{block name="hero_section"}
    <div class="mars-hero">
        <div class="hero-content">
            <h1 class="hero-title">
                <i class="fas fa-rocket"></i>
                Verso Marte
                <span class="mars-icon"></span>
            </h1>
            <p class="hero-subtitle">Il viaggio verso il pianeta rosso inizia qui</p>
            <div class="hero-stats">
                <div class="stat-card">
                    <i class="fas fa-globe-americas"></i>
                    <h3>225M km</h3>
                    <p>Distanza da Terra</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-clock"></i>
                    <h3>7-9 mesi</h3>
                    <p>Durata viaggio</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <h3>6-8</h3>
                    <p>Membri equipaggio</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-flag"></i>
                    <h3>2030</h3>
                    <p>Anno obiettivo</p>
                </div>
            </div>
        </div>
    </div>
{/block}

{block name="content"}
    {* Featured Articles Section *}
    {if isset($featured_articles) && $featured_articles}
        <section class="featured-missions">
            <h2 class="section-title">
                <i class="fas fa-star"></i>
                Missioni in Evidenza
            </h2>
            <div class="articles-grid">
                {foreach from=$featured_articles item=article}
                    <article class="article-card featured">
                        <div class="article-card-content">
                            <h3 class="article-card-title">
                                <a href="{$settings.SITE_URL}/article/{$article.id}">
                                    <i class="fas fa-satellite"></i>
                                    {$article.title|escape}
                                </a>
                            </h3>
                            <div class="article-meta">
                                <span><i class="fas fa-calendar"></i> {$article.created_at|date_format:"%d/%m/%Y"}</span>
                                {if isset($article.author)}
                                    <span><i class="fas fa-user-astronaut"></i> {$article.author|escape}</span>
                                {/if}
                                {if isset($article.category)}
                                    <span><i class="fas fa-tag"></i> {$article.category|escape}</span>
                                {/if}
                            </div>
                            <div class="article-card-excerpt">
                                {if $article.excerpt}
                                    {$article.excerpt|escape|truncate:200}
                                {elseif $article.content}
                                    {$article.content|strip_tags|truncate:200}
                                {/if}
                            </div>
                            <a href="{$settings.SITE_URL}/article/{$article.id}" class="btn mars-btn">
                                <i class="fas fa-rocket"></i>
                                Leggi Missione
                            </a>
                        </div>
                    </article>
                {/foreach}
            </div>
        </section>
    {/if}

    {* Latest Articles Section *}
    {if isset($articles) && $articles}
        <section class="latest-transmissions">
            <h2 class="section-title">
                <i class="fas fa-broadcast-tower"></i>
                Ultime Trasmissioni da Terra
            </h2>
            <div class="articles-grid">
                {foreach from=$articles item=article}
                    <article class="article-card">
                        <div class="article-card-content">
                            <h3 class="article-card-title">
                                <a href="{$settings.SITE_URL}/article/{$article.id}">
                                    <i class="fas fa-newspaper"></i>
                                    {$article.title|escape}
                                </a>
                            </h3>
                            <div class="article-meta">
                                <span><i class="fas fa-calendar"></i> Sol {$article.created_at|date_format:"%j"}</span>
                                {if isset($article.author)}
                                    <span><i class="fas fa-user-astronaut"></i> {$article.author|escape}</span>
                                {/if}
                            </div>
                            <div class="article-card-excerpt">
                                {if $article.excerpt}
                                    {$article.excerpt|escape|truncate:150}
                                {elseif $article.content}
                                    {$article.content|strip_tags|truncate:150}
                                {/if}
                            </div>
                            <a href="{$settings.SITE_URL}/article/{$article.id}" class="read-more">
                                <i class="fas fa-arrow-right"></i>
                                Continua lettura
                            </a>
                        </div>
                    </article>
                {/foreach}
            </div>
        </section>
    {/if}

    {* Mission Phases Section *}
    <section class="mission-phases">
        <h2 class="section-title">
            <i class="fas fa-route"></i>
            Fasi della Missione Mars
        </h2>
        <div class="phases-timeline">
            <div class="phase-item">
                <div class="phase-icon">
                    <i class="fas fa-cogs"></i>
                </div>
                <div class="phase-content">
                    <h3>Preparazione</h3>
                    <p>Sviluppo tecnologico, training dell'equipaggio e test dei sistemi</p>
                </div>
            </div>
            <div class="phase-item">
                <div class="phase-icon">
                    <i class="fas fa-rocket"></i>
                </div>
                <div class="phase-content">
                    <h3>Lancio</h3>
                    <p>Decollo dalla Terra durante la finestra di lancio ottimale</p>
                </div>
            </div>
            <div class="phase-item">
                <div class="phase-icon">
                    <i class="fas fa-satellite"></i>
                </div>
                <div class="phase-content">
                    <h3>Viaggio</h3>
                    <p>7-9 mesi di navigazione nello spazio profondo verso Marte</p>
                </div>
            </div>
            <div class="phase-item">
                <div class="phase-icon">
                    <i class="fas fa-parachute-box"></i>
                </div>
                <div class="phase-content">
                    <h3>Atterraggio</h3>
                    <p>Discesa controllata e atterraggio sulla superficie marziana</p>
                </div>
            </div>
            <div class="phase-item">
                <div class="phase-icon">
                    <i class="fas fa-home"></i>
                </div>
                <div class="phase-content">
                    <h3>Colonizzazione</h3>
                    <p>Stabilimento della prima base permanente su Marte</p>
                </div>
            </div>
        </div>
    </section>

    {* Mars Facts Section *}
    <section class="mars-facts">
        <h2 class="section-title">
            <i class="fas fa-planet-mars"></i>
            Curiosità su Marte
        </h2>
        <div class="facts-grid">
            <div class="fact-card">
                <i class="fas fa-weight"></i>
                <h4>Gravità</h4>
                <p>38% della gravità terrestre</p>
            </div>
            <div class="fact-card">
                <i class="fas fa-clock"></i>
                <h4>Giorno (Sol)</h4>
                <p>24 ore e 37 minuti</p>
            </div>
            <div class="fact-card">
                <i class="fas fa-calendar-alt"></i>
                <h4>Anno marziano</h4>
                <p>687 giorni terrestri</p>
            </div>
            <div class="fact-card">
                <i class="fas fa-mountain"></i>
                <h4>Monte più alto</h4>
                <p>Olympus Mons (21 km)</p>
            </div>
            <div class="fact-card">
                <i class="fas fa-thermometer-empty"></i>
                <h4>Temperatura minima</h4>
                <p>-143°C ai poli</p>
            </div>
            <div class="fact-card">
                <i class="fas fa-wind"></i>
                <h4>Atmosfera</h4>
                <p>95% anidride carbonica</p>
            </div>
        </div>
    </section>

    {* Call to Action *}
    <section class="mars-cta">
        <div class="cta-content">
            <h2>
                <i class="fas fa-user-plus"></i>
                Unisciti alla Missione
            </h2>
            <p>Vuoi far parte del viaggio verso Marte? Iscriviti alla nostra newsletter per ricevere aggiornamenti sulla missione.</p>
            <div class="cta-buttons">
                <a href="{$settings.SITE_URL}/newsletter" class="btn mars-btn">
                    <i class="fas fa-envelope"></i>
                    Iscriviti alla Newsletter
                </a>
                <a href="{$settings.SITE_URL}/about" class="btn btn-secondary">
                    <i class="fas fa-info-circle"></i>
                    Scopri di più
                </a>
            </div>
        </div>
    </section>
{/block}

{block name="head_extra"}
    <style>
        /* Mars Hero Section */
        .mars-hero {
            background: var(--nebula-gradient);
            padding: 3rem 0;
            margin: -2rem -2rem 2rem -2rem;
            border-radius: 0 0 var(--border-radius) var(--border-radius);
            text-align: center;
            border-bottom: 3px solid var(--mars-red);
        }

        .hero-title {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.7);
        }

        .hero-subtitle {
            font-size: 1.3rem;
            color: var(--starlight);
            opacity: 0.9;
            margin-bottom: 2rem;
        }

        .hero-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }

        .stat-card {
            background: rgba(26, 26, 46, 0.7);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            border: 1px solid var(--mars-red);
            backdrop-filter: blur(10px);
        }

        .stat-card i {
            font-size: 2rem;
            color: var(--mars-orange);
            margin-bottom: 0.5rem;
        }

        .stat-card h3 {
            color: var(--gold-accent);
            font-size: 2rem;
            margin: 0.5rem 0;
        }

        .stat-card p {
            color: var(--starlight);
            opacity: 0.8;
            margin: 0;
        }

        /* Section Styles */
        .section-title {
            font-family: var(--font-heading);
            color: var(--mars-orange);
            font-size: 2rem;
            margin: 3rem 0 2rem 0;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .section-title i {
            margin-right: 0.5rem;
            color: var(--gold-accent);
        }

        /* Mission Phases Timeline */
        .phases-timeline {
            display: flex;
            flex-direction: column;
            gap: 2rem;
            margin: 2rem 0;
        }

        .phase-item {
            display: flex;
            align-items: center;
            background: rgba(30, 58, 138, 0.2);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            border-left: 4px solid var(--mars-red);
            transition: all 0.3s ease;
        }

        .phase-item:hover {
            border-left-color: var(--gold-accent);
            transform: translateX(10px);
        }

        .phase-icon {
            width: 60px;
            height: 60px;
            background: var(--mars-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1.5rem;
            box-shadow: var(--shadow-mars);
        }

        .phase-icon i {
            color: white;
            font-size: 1.5rem;
        }

        .phase-content h3 {
            color: var(--gold-accent);
            margin-bottom: 0.5rem;
        }

        /* Mars Facts Grid */
        .facts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .fact-card {
            background: var(--nebula-gradient);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            text-align: center;
            border: 1px solid rgba(205, 92, 92, 0.3);
            transition: all 0.3s ease;
        }

        .fact-card:hover {
            border-color: var(--mars-orange);
            transform: translateY(-5px);
        }

        .fact-card i {
            font-size: 2.5rem;
            color: var(--mars-orange);
            margin-bottom: 1rem;
        }

        .fact-card h4 {
            color: var(--gold-accent);
            margin-bottom: 0.5rem;
        }

        .fact-card p {
            color: var(--starlight);
            opacity: 0.9;
        }

        /* Call to Action */
        .mars-cta {
            background: var(--mars-gradient);
            padding: 3rem;
            border-radius: var(--border-radius);
            text-align: center;
            margin: 3rem 0;
            box-shadow: var(--shadow-mars);
        }

        .mars-cta h2 {
            color: white;
            margin-bottom: 1rem;
        }

        .mars-cta p {
            color: white;
            opacity: 0.9;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid white;
        }

        .btn-secondary:hover {
            background: white;
            color: var(--mars-red);
        }

        /* Featured Articles */
        .article-card.featured {
            border: 2px solid var(--gold-accent);
            background: var(--mars-gradient);
        }

        .article-card.featured .article-card-title a {
            color: white;
        }

        .article-card.featured .article-meta span {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .article-card.featured .article-card-excerpt {
            color: white;
            opacity: 0.9;
        }

        .mars-btn {
            background: var(--gold-accent);
            color: var(--deep-space);
            font-weight: 600;
        }

        .mars-btn:hover {
            background: var(--starlight);
            color: var(--deep-space);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .phases-timeline {
                padding: 0 1rem;
            }
            
            .phase-item {
                flex-direction: column;
                text-align: center;
            }
            
            .phase-icon {
                margin: 0 0 1rem 0;
            }
        }
    </style>
{/block}