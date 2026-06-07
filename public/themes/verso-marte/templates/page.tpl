{* Template pagina per il tema Verso Marte *}
{extends file="layout.tpl"}

{block name="title"}
    {$page.title|escape} - {if isset($settings.site_title)}{$settings.site_title|escape}{else}Verso Marte{/if}
{/block}

{block name="description"}
    {if $page.excerpt}
        {$page.excerpt|escape|truncate:160}
    {else}
        {$page.content|strip_tags|truncate:160}
    {/if}
{/block}

{block name="content"}
    <div class="mars-page">
        {* Page Header *}
        <header class="page-header">
            <div class="mission-badge">
                <i class="fas fa-file-alt"></i>
                <span>Documento Mission Control</span>
            </div>
            
            <h1 class="page-title">
                <i class="fas fa-scroll"></i>
                {$page.title|escape}
            </h1>
            
            <div class="page-meta">
                <div class="meta-group">
                    <i class="fas fa-calendar-alt"></i>
                    <span class="meta-label">Ultima modifica:</span>
                    <span class="meta-value">{$page.updated_at|date_format:"%d %B %Y"}</span>
                </div>
                
                {if isset($page.author) && $page.author}
                    <div class="meta-group">
                        <i class="fas fa-user-astronaut"></i>
                        <span class="meta-label">Responsabile documento:</span>
                        <span class="meta-value">{$page.author|escape}</span>
                    </div>
                {/if}
                
                {if isset($page.reading_time)}
                    <div class="meta-group">
                        <i class="fas fa-clock"></i>
                        <span class="meta-label">Tempo lettura:</span>
                        <span class="meta-value">{$page.reading_time} minuti</span>
                    </div>
                {/if}
            </div>
        </header>

        {* Page Content *}
        <div class="page-body">
            {if $page.featured_image}
                <div class="page-featured-image">
                    <img src="{$page.featured_image}" alt="{$page.title|escape}" class="img-fluid">
                    <div class="image-overlay">
                        <i class="fas fa-camera"></i>
                        <span>Documentazione fotografica missione</span>
                    </div>
                </div>
            {/if}
            
            <div class="page-content">
                {$page.content}
            </div>
        </div>

        {* Page Footer *}
        <footer class="page-footer">
            <div class="document-status">
                <i class="fas fa-check-circle"></i>
                <span>Documento verificato e approvato da Mission Control</span>
            </div>
            
            {* Page Navigation - if it's part of a series *}
            {if isset($page_navigation) && ($page_navigation.previous || $page_navigation.next)}
                <div class="page-navigation">
                    {if isset($page_navigation.previous) && $page_navigation.previous}
                        <div class="nav-previous">
                            <a href="{$settings.SITE_URL}/page/{$page_navigation.previous.slug}" class="nav-link">
                                <i class="fas fa-chevron-left"></i>
                                <div class="nav-content">
                                    <span class="nav-label">Documento precedente</span>
                                    <span class="nav-title">{$page_navigation.previous.title|escape|truncate:40}</span>
                                </div>
                            </a>
                        </div>
                    {/if}
                    
                    {if isset($page_navigation.next) && $page_navigation.next}
                        <div class="nav-next">
                            <a href="{$settings.SITE_URL}/page/{$page_navigation.next.slug}" class="nav-link">
                                <div class="nav-content">
                                    <span class="nav-label">Documento successivo</span>
                                    <span class="nav-title">{$page_navigation.next.title|escape|truncate:40}</span>
                                </div>
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </div>
                    {/if}
                </div>
            {/if}

            {* Back to Top *}
            <div class="back-to-top">
                <a href="#top" class="btn mars-btn">
                    <i class="fas fa-rocket"></i>
                    Torna in orbita
                </a>
            </div>
        </footer>
    </div>

    {* Related Pages (if applicable) *}
    {if isset($related_pages) && $related_pages}
        <section class="related-documents">
            <h2 class="section-title">
                <i class="fas fa-folder-open"></i>
                Documenti Correlati
            </h2>
            <div class="related-grid">
                {foreach from=$related_pages item=related_page}
                    <div class="related-page">
                        <div class="related-content">
                            <h3 class="related-title">
                                <a href="{$settings.SITE_URL}/page/{$related_page.slug}">
                                    <i class="fas fa-file-alt"></i>
                                    {$related_page.title|escape|truncate:50}
                                </a>
                            </h3>
                            <div class="related-meta">
                                <span><i class="fas fa-calendar"></i> {$related_page.updated_at|date_format:"%d/%m/%Y"}</span>
                            </div>
                            {if $related_page.excerpt}
                                <p class="related-excerpt">{$related_page.excerpt|escape|truncate:100}</p>
                            {/if}
                        </div>
                    </div>
                {/foreach}
            </div>
        </section>
    {/if}
{/block}

{block name="head_extra"}
    <style>
        /* Mars Page Styles */
        .mars-page {
            background: rgba(26, 26, 46, 0.8);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-mars);
            border: 1px solid rgba(205, 92, 92, 0.3);
        }

        /* Page Header */
        .page-header {
            background: var(--nebula-gradient);
            padding: 2rem;
            border-bottom: 3px solid var(--mars-red);
        }

        .mission-badge {
            display: inline-flex;
            align-items: center;
            background: var(--mars-gradient);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 1rem;
            box-shadow: var(--shadow-mars);
        }

        .mission-badge i {
            margin-right: 0.5rem;
        }

        .page-title {
            font-family: var(--font-heading);
            font-size: 2.5rem;
            color: var(--gold-accent);
            margin-bottom: 1.5rem;
            line-height: 1.2;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.7);
        }

        .page-title i {
            color: var(--mars-orange);
            margin-right: 0.5rem;
        }

        /* Page Meta Information */
        .page-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .meta-group {
            display: flex;
            align-items: center;
            background: rgba(26, 26, 46, 0.7);
            padding: 0.75rem;
            border-radius: var(--border-radius);
            border: 1px solid rgba(30, 58, 138, 0.3);
        }

        .meta-group i {
            color: var(--mars-orange);
            margin-right: 0.5rem;
            width: 16px;
        }

        .meta-label {
            color: var(--starlight);
            opacity: 0.8;
            margin-right: 0.5rem;
        }

        .meta-value {
            color: var(--gold-accent);
            font-weight: 500;
        }

        /* Page Body */
        .page-body {
            padding: 2rem;
        }

        .page-featured-image {
            position: relative;
            margin-bottom: 2rem;
            border-radius: var(--border-radius);
            overflow: hidden;
        }

        .page-featured-image img {
            width: 100%;
            height: auto;
            display: block;
        }

        .image-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.8));
            color: white;
            padding: 1rem;
            display: flex;
            align-items: center;
        }

        .image-overlay i {
            margin-right: 0.5rem;
            color: var(--gold-accent);
        }

        .page-content {
            line-height: 1.8;
            font-size: 1.1rem;
        }

        .page-content h2,
        .page-content h3,
        .page-content h4 {
            color: var(--mars-orange);
            margin: 2rem 0 1rem 0;
            font-family: var(--font-heading);
        }

        .page-content h2 {
            border-bottom: 2px solid var(--mars-red);
            padding-bottom: 0.5rem;
        }

        .page-content p {
            margin-bottom: 1.5rem;
            color: var(--starlight);
        }

        .page-content blockquote {
            background: rgba(205, 92, 92, 0.1);
            border-left: 4px solid var(--mars-red);
            padding: 1rem 1.5rem;
            margin: 2rem 0;
            border-radius: 0 var(--border-radius) var(--border-radius) 0;
            font-style: italic;
            color: var(--starlight);
        }

        .page-content ul,
        .page-content ol {
            margin-left: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .page-content li {
            margin-bottom: 0.5rem;
            color: var(--starlight);
        }

        .page-content a {
            color: var(--mars-orange);
            text-decoration: underline;
        }

        .page-content a:hover {
            color: var(--gold-accent);
        }

        /* Tables */
        .page-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 2rem 0;
            background: rgba(26, 26, 46, 0.5);
            border-radius: var(--border-radius);
            overflow: hidden;
        }

        .page-content th,
        .page-content td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid rgba(205, 92, 92, 0.2);
        }

        .page-content th {
            background: var(--mars-gradient);
            color: white;
            font-family: var(--font-heading);
            font-weight: 600;
        }

        .page-content td {
            color: var(--starlight);
        }

        /* Page Footer */
        .page-footer {
            background: rgba(26, 26, 46, 0.5);
            padding: 2rem;
            border-top: 1px solid rgba(205, 92, 92, 0.3);
        }

        .document-status {
            text-align: center;
            color: var(--mars-dust);
            margin-bottom: 2rem;
            padding: 1rem;
            background: rgba(30, 58, 138, 0.1);
            border-radius: var(--border-radius);
            border: 1px solid rgba(30, 58, 138, 0.2);
        }

        .document-status i {
            color: var(--mars-orange);
            margin-right: 0.5rem;
        }

        /* Page Navigation */
        .page-navigation {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .nav-previous {
            text-align: left;
        }

        .nav-next {
            text-align: right;
        }

        .nav-link {
            display: flex;
            align-items: center;
            background: rgba(30, 58, 138, 0.2);
            padding: 1rem;
            border-radius: var(--border-radius);
            color: var(--starlight);
            text-decoration: none;
            transition: all 0.3s ease;
            border: 1px solid rgba(30, 58, 138, 0.3);
        }

        .nav-link:hover {
            background: rgba(30, 58, 138, 0.4);
            border-color: var(--mars-orange);
            transform: translateY(-2px);
            color: var(--starlight);
        }

        .nav-content {
            flex-grow: 1;
        }

        .nav-label {
            display: block;
            font-size: 0.85rem;
            color: var(--mars-dust);
            margin-bottom: 0.25rem;
        }

        .nav-title {
            display: block;
            color: var(--gold-accent);
            font-weight: 500;
        }

        /* Back to Top */
        .back-to-top {
            text-align: center;
        }

        .mars-btn {
            background: var(--mars-gradient);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--border-radius);
            font-family: var(--font-heading);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-mars);
        }

        .mars-btn i {
            margin-right: 0.5rem;
        }

        .mars-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(205, 92, 92, 0.4);
            color: white;
        }

        /* Related Documents */
        .related-documents {
            margin-top: 3rem;
        }

        .section-title {
            font-family: var(--font-heading);
            color: var(--mars-orange);
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .section-title i {
            color: var(--gold-accent);
            margin-right: 0.5rem;
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
        }

        .related-page {
            background: rgba(26, 26, 46, 0.7);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            border: 1px solid rgba(205, 92, 92, 0.2);
            transition: all 0.3s ease;
        }

        .related-page:hover {
            border-color: var(--mars-orange);
            transform: translateY(-3px);
        }

        .related-title {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .related-title a {
            color: var(--gold-accent);
            text-decoration: none;
        }

        .related-title i {
            color: var(--mars-orange);
            margin-right: 0.5rem;
        }

        .related-meta {
            font-size: 0.85rem;
            color: var(--mars-dust);
            margin-bottom: 0.5rem;
        }

        .related-meta i {
            margin-right: 0.25rem;
        }

        .related-excerpt {
            color: var(--starlight);
            opacity: 0.8;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .page-title {
                font-size: 2rem;
            }
            
            .page-meta {
                flex-direction: column;
            }
            
            .page-navigation {
                grid-template-columns: 1fr;
            }
            
            .nav-next {
                text-align: left;
            }
            
            .related-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Smooth Scrolling */
        html {
            scroll-behavior: smooth;
        }
    </style>
{/block}