{* Footer partial per il tema Verso Marte - Mars & Space Inspired *}
<footer class="site-footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-main">
                <div class="footer-section footer-about">
                    <h4>
                        <i class="fas fa-rocket"></i>
                        {if isset($settings.site_title)}{$settings.site_title|escape}{else}Verso Marte{/if}
                    </h4>
                    {if isset($settings.site_description)}
                        <p>{$settings.site_description|escape}</p>
                    {else}
                        <p>Il viaggio verso il pianeta rosso inizia qui. Unisciti alla missione più ambiziosa dell'umanità.</p>
                    {/if}
                    
                    {* Social Links *}
                    <div class="social-links">
                        {if isset($settings.social_twitter) && $settings.social_twitter}
                            <a href="{$settings.social_twitter}" target="_blank" rel="noopener" class="social-link twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                        {/if}
                        {if isset($settings.social_facebook) && $settings.social_facebook}
                            <a href="{$settings.social_facebook}" target="_blank" rel="noopener" class="social-link facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                        {/if}
                        {if isset($settings.social_youtube) && $settings.social_youtube}
                            <a href="{$settings.social_youtube}" target="_blank" rel="noopener" class="social-link youtube">
                                <i class="fab fa-youtube"></i>
                            </a>
                        {/if}
                        {if isset($settings.social_instagram) && $settings.social_instagram}
                            <a href="{$settings.social_instagram}" target="_blank" rel="noopener" class="social-link instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                        {/if}
                    </div>
                </div>
                
                <div class="footer-section footer-links">
                    <h4><i class="fas fa-map"></i> Navigazione</h4>
                    <ul class="footer-menu">
                        <li><a href="{$settings.SITE_URL}/"><i class="fas fa-home"></i> Base Terra</a></li>
                        <li><a href="{$settings.SITE_URL}/articles"><i class="fas fa-newspaper"></i> Archivio Missioni</a></li>
                        <li><a href="{$settings.SITE_URL}/categories"><i class="fas fa-tags"></i> Settori</a></li>
                        <li><a href="{$settings.SITE_URL}/about"><i class="fas fa-user-astronaut"></i> Equipaggio</a></li>
                        <li><a href="{$settings.SITE_URL}/contact"><i class="fas fa-satellite"></i> Contatto</a></li>
                        {if isset($user) && $user}
                            <li><a href="{$settings.SITE_URL}/admin"><i class="fas fa-rocket"></i> Mission Control</a></li>
                        {/if}
                    </ul>
                </div>
                
                <div class="footer-section footer-mission">
                    <h4><i class="fas fa-info-circle"></i> Mission Status</h4>
                    <div class="mission-info">
                        <div class="status-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Prossima finestra: 2026</span>
                        </div>
                        <div class="status-item">
                            <i class="fas fa-globe-americas"></i>
                            <span>Distanza: 225M km</span>
                        </div>
                        <div class="status-item">
                            <i class="fas fa-clock"></i>
                            <span>Sol marziano: {$smarty.now|date_format:"%j"}</span>
                        </div>
                        <div class="status-item">
                            <i class="fas fa-satellite-dish"></i>
                            <span class="status-active">Signal: Strong</span>
                        </div>
                    </div>
                </div>
                
                <div class="footer-section footer-newsletter">
                    <h4><i class="fas fa-envelope"></i> Trasmissioni</h4>
                    <p>Ricevi aggiornamenti sulla missione</p>
                    <form class="newsletter-form" action="{$settings.SITE_URL}/newsletter" method="post">
                        <div class="input-group">
                            <input type="email" name="email" placeholder="La tua frequenza radio..." required class="newsletter-input">
                            <button type="submit" class="newsletter-btn">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="footer-copyright">
                    <p>
                        <i class="fas fa-rocket"></i>
                        &copy; {$smarty.now|date_format:"%Y"} 
                        {if isset($settings.site_title)}{$settings.site_title|escape}{else}Verso Marte{/if}
                        - Tutti i diritti riservati
                    </p>
                </div>
                
                <div class="footer-credits">
                    <p>
                        Powered by <strong>swCMS</strong>
                        <i class="fas fa-user-astronaut"></i>
                    </p>
                </div>
                
                <div class="footer-quote">
                    <p class="mars-quote">
                        <i class="fas fa-quote-left"></i>
                        "Marte è lì, aspettando di essere raggiunto."
                        <strong>- Buzz Aldrin</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
/* Mars Footer Styles */
.site-footer {
    background: var(--space-surface);
    border-top: 3px solid var(--mars-red);
    padding: 3rem 0 1rem;
    margin-top: 3rem;
    color: var(--starlight);
}

.footer-main {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-bottom: 2rem;
}

.footer-section h4 {
    color: var(--mars-orange);
    font-family: var(--font-heading);
    font-size: 1.2rem;
    margin-bottom: 1rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.footer-section h4 i {
    color: var(--gold-accent);
    margin-right: 0.5rem;
}

.footer-section p {
    color: var(--starlight);
    opacity: 0.8;
    line-height: 1.6;
    margin-bottom: 1rem;
}

/* Social Links */
.social-links {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
}

.social-link {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
    font-size: 1.1rem;
}

.social-link.twitter { background: #1da1f2; }
.social-link.facebook { background: #3b5998; }
.social-link.youtube { background: #ff0000; }
.social-link.instagram { background: linear-gradient(45deg, #405de6, #5851db, #833ab4, #c13584, #e1306c, #fd1d1d); }

.social-link:hover {
    transform: translateY(-3px) scale(1.1);
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

/* Footer Menu */
.footer-menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-menu li {
    margin-bottom: 0.5rem;
}

.footer-menu a {
    color: var(--starlight);
    text-decoration: none;
    transition: color 0.3s ease;
    display: flex;
    align-items: center;
}

.footer-menu a i {
    color: var(--mars-orange);
    margin-right: 0.5rem;
    width: 16px;
}

.footer-menu a:hover {
    color: var(--gold-accent);
}

/* Mission Info */
.mission-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.status-item {
    display: flex;
    align-items: center;
    color: var(--starlight);
    opacity: 0.8;
}

.status-item i {
    color: var(--mars-orange);
    margin-right: 0.5rem;
    width: 16px;
}

.status-active {
    color: #2ed573;
    font-weight: 500;
}

/* Newsletter Form */
.newsletter-form {
    margin-top: 1rem;
}

.input-group {
    display: flex;
    background: rgba(26, 26, 46, 0.7);
    border: 1px solid rgba(205, 92, 92, 0.3);
    border-radius: var(--border-radius);
    overflow: hidden;
}

.newsletter-input {
    flex-grow: 1;
    padding: 0.75rem;
    background: transparent;
    border: none;
    color: var(--starlight);
    font-family: var(--font-body);
}

.newsletter-input:focus {
    outline: none;
}

.newsletter-input::placeholder {
    color: var(--mars-dust);
    opacity: 0.7;
}

.newsletter-btn {
    background: var(--mars-gradient);
    border: none;
    padding: 0.75rem 1rem;
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
}

.newsletter-btn:hover {
    background: var(--gold-accent);
    color: var(--deep-space);
}

/* Footer Bottom */
.footer-bottom {
    border-top: 1px solid rgba(205, 92, 92, 0.3);
    padding-top: 2rem;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    text-align: center;
}

.footer-copyright,
.footer-credits,
.footer-quote {
    color: var(--starlight);
    opacity: 0.8;
}

.mars-quote {
    font-style: italic;
    position: relative;
}

.mars-quote i {
    color: var(--mars-orange);
    opacity: 0.5;
}

.mars-quote strong {
    color: var(--gold-accent);
}

/* Responsive */
@media (max-width: 768px) {
    .site-footer {
        padding: 2rem 0 1rem;
    }
    
    .footer-main {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .footer-bottom {
        grid-template-columns: 1fr;
        text-align: center;
        gap: 1rem;
    }
    
    .social-links {
        justify-content: center;
    }
    
    .input-group {
        flex-direction: column;
    }
    
    .newsletter-btn {
        border-radius: 0 0 var(--border-radius) var(--border-radius);
    }
}
</style>