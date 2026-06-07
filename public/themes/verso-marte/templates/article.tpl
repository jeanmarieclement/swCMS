{* Template articolo per il tema Verso Marte *}
{extends file="layout.tpl"}

{block name="title"}
    {$article.title|escape} - {if isset($settings.site_title)}{$settings.site_title|escape}{else}Verso Marte{/if}
{/block}

{block name="description"}
    {if $article.excerpt}
        {$article.excerpt|escape|truncate:160}
    {else}
        {$article.content|strip_tags|truncate:160}
    {/if}
{/block}

{block name="keywords"}
    {if isset($article.tags) && $article.tags}
        {foreach from=$article.tags item=tag name=tags}
            {$tag.name|escape}{if !$smarty.foreach.tags.last}, {/if}
        {/foreach}
    {else}
        Marte, spazio, esplorazione spaziale, missione Mars
    {/if}
{/block}

{block name="content"}
    <article class="mars-article">
        {* Article Header *}
        <header class="article-header">
            <div class="mission-badge">
                <i class="fas fa-satellite"></i>
                <span>Trasmissione da Mission Control</span>
            </div>
            
            <h1 class="article-title">
                <i class="fas fa-broadcast-tower"></i>
                {$article.title|escape}
            </h1>
            
            <div class="article-meta-extended">
                <div class="meta-group">
                    <i class="fas fa-calendar-alt"></i>
                    <span class="meta-label">Data Terra:</span>
                    <span class="meta-value">{$article.created_at|date_format:"%d %B %Y"}</span>
                </div>
                
                <div class="meta-group">
                    <i class="fas fa-clock"></i>
                    <span class="meta-label">Sol marziano:</span>
                    <span class="meta-value">{$article.created_at|date_format:"%j"}</span>
                </div>
                
                {if isset($article.author) && $article.author}
                    <div class="meta-group">
                        <i class="fas fa-user-astronaut"></i>
                        <span class="meta-label">Comandante:</span>
                        <span class="meta-value">{$article.author|escape}</span>
                    </div>
                {/if}
                
                {if isset($article.category) && $article.category}
                    <div class="meta-group">
                        <i class="fas fa-folder"></i>
                        <span class="meta-label">Settore:</span>
                        <span class="meta-value">
                            <a href="{$settings.SITE_URL}/category/{$article.category.slug}">{$article.category.name|escape}</a>
                        </span>
                    </div>
                {/if}
                
                {if isset($article.reading_time)}
                    <div class="meta-group">
                        <i class="fas fa-book-reader"></i>
                        <span class="meta-label">Tempo lettura:</span>
                        <span class="meta-value">{$article.reading_time} min</span>
                    </div>
                {/if}
            </div>
            
            {* Tags *}
            {if isset($article.tags) && $article.tags}
                <div class="article-tags">
                    <i class="fas fa-tags"></i>
                    <span class="tags-label">Hashtag missione:</span>
                    {foreach from=$article.tags item=tag}
                        <a href="{$settings.SITE_URL}/tag/{$tag.slug}" class="tag-link">
                            #{$tag.name|escape}
                        </a>
                    {/foreach}
                </div>
            {/if}
        </header>

        {* Article Content *}
        <div class="article-body">
            {if $article.featured_image}
                <div class="article-featured-image">
                    <img src="{$article.featured_image}" alt="{$article.title|escape}" class="img-fluid">
                    <div class="image-overlay">
                        <i class="fas fa-camera"></i>
                        <span>Immagine catturata dalla sonda Mars</span>
                    </div>
                </div>
            {/if}
            
            <div class="article-content">
                {$article.content}
            </div>
        </div>

        {* Article Footer *}
        <footer class="article-footer">
            <div class="mission-status">
                <i class="fas fa-satellite-dish"></i>
                <span>Fine trasmissione - Signal strength: Strong</span>
            </div>
            
            {* Social Sharing *}
            <div class="article-sharing">
                <span class="sharing-label">
                    <i class="fas fa-share-alt"></i>
                    Condividi questa scoperta:
                </span>
                <div class="sharing-buttons">
                    <a href="https://twitter.com/intent/tweet?text={$article.title|escape|url_encode}&url={$settings.SITE_URL}/article/{$article.id}" 
                       target="_blank" class="share-btn twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={$settings.SITE_URL}/article/{$article.id}" 
                       target="_blank" class="share-btn facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url={$settings.SITE_URL}/article/{$article.id}" 
                       target="_blank" class="share-btn linkedin">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <a href="mailto:?subject={$article.title|escape}&body={$settings.SITE_URL}/article/{$article.id}" 
                       class="share-btn email">
                        <i class="fas fa-envelope"></i>
                    </a>
                </div>
            </div>
            
            {* Navigation between articles *}
            <div class="article-navigation">
                {if isset($previous_article) && $previous_article}
                    <div class="nav-previous">
                        <a href="{$settings.SITE_URL}/article/{$previous_article.id}" class="nav-link">
                            <i class="fas fa-chevron-left"></i>
                            <div class="nav-content">
                                <span class="nav-label">Trasmissione precedente</span>
                                <span class="nav-title">{$previous_article.title|escape|truncate:40}</span>
                            </div>
                        </a>
                    </div>
                {/if}
                
                {if isset($next_article) && $next_article}
                    <div class="nav-next">
                        <a href="{$settings.SITE_URL}/article/{$next_article.id}" class="nav-link">
                            <div class="nav-content">
                                <span class="nav-label">Prossima trasmissione</span>
                                <span class="nav-title">{$next_article.title|escape|truncate:40}</span>
                            </div>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                {/if}
            </div>
        </footer>
    </article>

    {* Related Articles *}
    {if isset($related_articles) && $related_articles}
        <section class="related-missions">
            <h2 class="section-title">
                <i class="fas fa-rocket"></i>
                Missioni Correlate
            </h2>
            <div class="related-grid">
                {foreach from=$related_articles item=related_article}
                    <article class="related-article">
                        <div class="related-content">
                            <h3 class="related-title">
                                <a href="{$settings.SITE_URL}/article/{$related_article.id}">
                                    <i class="fas fa-newspaper"></i>
                                    {$related_article.title|escape|truncate:60}
                                </a>
                            </h3>
                            <div class="related-meta">
                                <span><i class="fas fa-calendar"></i> {$related_article.created_at|date_format:"%d/%m/%Y"}</span>
                                {if isset($related_article.category)}
                                    <span><i class="fas fa-tag"></i> {$related_article.category.name|escape}</span>
                                {/if}
                            </div>
                        </div>
                    </article>
                {/foreach}
            </div>
        </section>
    {/if}

    {* Comments Section *}
    {if isset($comments_enabled) && $comments_enabled}
        <section class="mission-log">
            <h2 class="section-title">
                <i class="fas fa-comments"></i>
                Log della Missione
            </h2>
            <div class="comments-section">
                {* Comments would be rendered here *}
                <div class="comment-form">
                    <h3><i class="fas fa-pencil-alt"></i> Aggiungi al log della missione</h3>
                    <form class="mars-form" method="post" action="{$settings.SITE_URL}/comments/add">
                        <input type="hidden" name="article_id" value="{$article.id}">
                        <div class="form-group">
                            <label for="comment_author"><i class="fas fa-user"></i> Nome astronauta:</label>
                            <input type="text" id="comment_author" name="author" required class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="comment_email"><i class="fas fa-envelope"></i> Frequenza radio:</label>
                            <input type="email" id="comment_email" name="email" required class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="comment_content"><i class="fas fa-comment"></i> Messaggio:</label>
                            <textarea id="comment_content" name="content" required class="form-control" rows="4" 
                                    placeholder="Scrivi il tuo contributo alla missione..."></textarea>
                        </div>
                        <button type="submit" class="btn mars-btn">
                            <i class="fas fa-paper-plane"></i>
                            Invia trasmissione
                        </button>
                    </form>
                </div>
            </div>
        </section>
    {/if}
{/block}

{block name="head_extra"}
    <style>
        /* Mars Article Styles */
        .mars-article {
            background: rgba(26, 26, 46, 0.8);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-mars);
            border: 1px solid rgba(205, 92, 92, 0.3);
        }

        /* Article Header */
        .article-header {
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

        .article-title {
            font-family: var(--font-heading);
            font-size: 2.5rem;
            color: var(--gold-accent);
            margin-bottom: 1.5rem;
            line-height: 1.2;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.7);
        }

        .article-title i {
            color: var(--mars-orange);
            margin-right: 0.5rem;
        }

        /* Extended Meta Information */
        .article-meta-extended {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
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

        .meta-value a {
            color: var(--gold-accent);
        }

        /* Tags */
        .article-tags {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .article-tags i {
            color: var(--mars-orange);
            margin-right: 0.5rem;
        }

        .tags-label {
            color: var(--starlight);
            margin-right: 0.5rem;
        }

        .tag-link {
            background: var(--mars-gradient);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 0.85rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .tag-link:hover {
            background: var(--gold-accent);
            color: var(--deep-space);
            transform: translateY(-2px);
        }

        /* Article Body */
        .article-body {
            padding: 2rem;
        }

        .article-featured-image {
            position: relative;
            margin-bottom: 2rem;
            border-radius: var(--border-radius);
            overflow: hidden;
        }

        .article-featured-image img {
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

        .article-content {
            line-height: 1.8;
            font-size: 1.1rem;
        }

        .article-content h2,
        .article-content h3,
        .article-content h4 {
            color: var(--mars-orange);
            margin: 2rem 0 1rem 0;
            font-family: var(--font-heading);
        }

        .article-content p {
            margin-bottom: 1.5rem;
            color: var(--starlight);
        }

        .article-content blockquote {
            background: rgba(205, 92, 92, 0.1);
            border-left: 4px solid var(--mars-red);
            padding: 1rem 1.5rem;
            margin: 2rem 0;
            border-radius: 0 var(--border-radius) var(--border-radius) 0;
            font-style: italic;
            color: var(--starlight);
        }

        /* Article Footer */
        .article-footer {
            background: rgba(26, 26, 46, 0.5);
            padding: 2rem;
            border-top: 1px solid rgba(205, 92, 92, 0.3);
        }

        .mission-status {
            text-align: center;
            color: var(--mars-dust);
            margin-bottom: 2rem;
            font-style: italic;
        }

        .mission-status i {
            color: var(--mars-orange);
            margin-right: 0.5rem;
        }

        /* Social Sharing */
        .article-sharing {
            text-align: center;
            margin-bottom: 2rem;
        }

        .sharing-label {
            display: block;
            color: var(--starlight);
            margin-bottom: 1rem;
            font-family: var(--font-heading);
        }

        .sharing-label i {
            color: var(--mars-orange);
            margin-right: 0.5rem;
        }

        .sharing-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .share-btn {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 1.2rem;
        }

        .share-btn.twitter { background: #1da1f2; }
        .share-btn.facebook { background: #3b5998; }
        .share-btn.linkedin { background: #0077b5; }
        .share-btn.email { background: var(--mars-gradient); }

        .share-btn:hover {
            transform: translateY(-3px) scale(1.1);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        /* Article Navigation */
        .article-navigation {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-top: 2rem;
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

        /* Related Articles */
        .related-missions {
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

        .related-article {
            background: rgba(26, 26, 46, 0.7);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            border: 1px solid rgba(205, 92, 92, 0.2);
            transition: all 0.3s ease;
        }

        .related-article:hover {
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
            display: flex;
            gap: 1rem;
            font-size: 0.85rem;
            color: var(--mars-dust);
        }

        .related-meta i {
            margin-right: 0.25rem;
        }

        /* Comments Section */
        .mission-log {
            margin-top: 3rem;
        }

        .comments-section {
            background: rgba(26, 26, 46, 0.7);
            border-radius: var(--border-radius);
            padding: 2rem;
            border: 1px solid rgba(30, 58, 138, 0.3);
        }

        .comment-form h3 {
            color: var(--mars-orange);
            margin-bottom: 1.5rem;
        }

        .comment-form h3 i {
            color: var(--gold-accent);
            margin-right: 0.5rem;
        }

        .mars-form .form-group {
            margin-bottom: 1.5rem;
        }

        .mars-form label {
            display: block;
            color: var(--starlight);
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .mars-form label i {
            color: var(--mars-orange);
            margin-right: 0.5rem;
        }

        .mars-form .form-control {
            width: 100%;
            padding: 0.75rem;
            background: rgba(26, 26, 46, 0.8);
            border: 1px solid rgba(205, 92, 92, 0.3);
            border-radius: var(--border-radius);
            color: var(--starlight);
            font-family: var(--font-body);
        }

        .mars-form .form-control:focus {
            outline: none;
            border-color: var(--mars-orange);
            box-shadow: 0 0 0 2px rgba(205, 92, 92, 0.2);
        }

        .mars-form textarea {
            resize: vertical;
            min-height: 120px;
        }

        .mars-form .mars-btn {
            background: var(--mars-gradient);
            border: none;
            padding: 0.75rem 1.5rem;
            font-family: var(--font-heading);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .article-title {
                font-size: 2rem;
            }
            
            .article-meta-extended {
                grid-template-columns: 1fr;
            }
            
            .article-navigation {
                grid-template-columns: 1fr;
            }
            
            .nav-next {
                text-align: left;
            }
            
            .sharing-buttons {
                justify-content: center;
            }
            
            .related-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
{/block}