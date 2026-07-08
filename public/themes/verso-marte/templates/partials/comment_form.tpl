{* Comment form per il tema Verso Marte - Mars Mission Log Style *}
<div class="mars-mission-log-form">
    <div class="log-form-header">
        <h3 class="log-form-title">
            <i class="fas fa-pencil-alt"></i>
            Aggiungi Trasmissione al Log
        </h3>
        <p class="log-form-description">
            Condividi le tue riflessioni sulla missione Mars con il team di controllo.
        </p>
    </div>

    <form class="mars-comment-form" method="post" action="/comments/store" id="comment-form">
        <input type="hidden" name="csrf_token" value="{$csrf_token}">
        <input type="hidden" name="post_id" value="{if isset($article)}{$article.id}{elseif isset($post)}{$post.id}{/if}">
        <input type="hidden" name="page_id" value="{if isset($page)}{$page.id}{/if}">
        <input type="hidden" name="parent_id" value="" id="parent_id">
        <input type="hidden" name="redirect_url" value="{$smarty.server.REQUEST_URI}">
        
        <div class="form-row">
            <div class="form-group">
                <label for="comment_author" class="form-label required">
                    <i class="fas fa-user-astronaut"></i>
                    Nome Astronauta
                </label>
                <input type="text" 
                       id="comment_author" 
                       name="author_name" 
                       required 
                       class="form-control"
                       placeholder="Il tuo nome o call sign..."
                       value="{if isset($user_display_name) && $user_display_name}{$user_display_name}{/if}"
                       {if isset($user_id) && $user_id}readonly{/if}>
                <div class="field-help">
                    <i class="fas fa-info-circle"></i>
                    <span>Il nome con cui apparirà la tua trasmissione</span>
                </div>
            </div>

            <div class="form-group">
                <label for="comment_email" class="form-label required">
                    <i class="fas fa-satellite-dish"></i>
                    Frequenza Radio (Email)
                </label>
                <input type="email" 
                       id="comment_email" 
                       name="author_email" 
                       {if !isset($user_id) || !$user_id}required{/if}
                       class="form-control"
                       placeholder="la-tua-frequenza@mars-mission.space"
                       value="{if isset($user_email) && $user_email}{$user_email}{/if}"
                       {if isset($user_id) && $user_id}readonly{/if}>
                <div class="field-help">
                    <i class="fas fa-info-circle"></i>
                    <span>Necessaria per le comunicazioni, non sarà pubblica</span>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="comment_website" class="form-label optional">
                <i class="fas fa-globe"></i>
                Sito Web (Opzionale)
            </label>
            <input type="url" 
                   id="comment_website" 
                   name="website" 
                   class="form-control"
                   placeholder="https://il-tuo-sito-spaziale.com">
            <div class="field-help">
                <i class="fas fa-info-circle"></i>
                <span>Il tuo sito web o profilo sociale</span>
            </div>
        </div>

        <div class="form-group">
            <label for="comment_content" class="form-label required">
                <i class="fas fa-comment"></i>
                Messaggio Trasmissione
            </label>
            <textarea id="comment_content" 
                      name="content" 
                      required 
                      class="form-control message-textarea" 
                      rows="6"
                      placeholder="Scrivi qui il tuo contributo alla missione Mars...&#10;&#10;Puoi condividere:&#10;• Riflessioni sull'articolo&#10;• Domande per il team&#10;• Idee per la missione&#10;• Esperienze personali"></textarea>
            <div class="character-counter">
                <span id="char-count">0</span> / <span class="max-chars">1000</span> caratteri
            </div>
        </div>

        {* GDPR/Privacy Consent *}
        <div class="form-group consent-group">
            <div class="consent-checkbox">
                <input type="checkbox" id="privacy_consent" name="privacy_consent" required class="form-checkbox">
                <label for="privacy_consent" class="checkbox-label">
                    <div class="checkbox-custom">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="consent-text">
                        <strong>Acconsento al trattamento dei miei dati</strong> per la pubblicazione del commento come da 
                        <a href="{$settings.SITE_URL}/privacy" target="_blank" class="privacy-link">
                            <i class="fas fa-external-link-alt"></i>
                            Informativa Privacy
                        </a>
                    </div>
                </label>
            </div>
        </div>

        {* Anti-spam measures *}
        <div class="form-group security-group">
            <label for="security_check" class="form-label required">
                <i class="fas fa-shield-alt"></i>
                Verifica Sicurezza
            </label>
            <div class="security-question">
                <p>Domanda di verifica: Quale pianeta stiamo esplorando in questa missione?</p>
                <input type="text" 
                       id="security_check" 
                       name="security_answer" 
                       required 
                       class="form-control security-input"
                       placeholder="Scrivi il nome del pianeta rosso...">
                <div class="field-help">
                    <i class="fas fa-robot"></i>
                    <span>Questa verifica ci aiuta a prevenire messaggi automatici</span>
                </div>
            </div>
        </div>

        {* Mission Guidelines *}
        <div class="mission-guidelines">
            <h4>
                <i class="fas fa-clipboard-list"></i>
                Linee Guida della Missione
            </h4>
            <ul class="guidelines-list">
                <li><i class="fas fa-check-circle"></i> Mantieni un tono rispettoso e costruttivo</li>
                <li><i class="fas fa-check-circle"></i> Condividi contenuti pertinenti alla missione</li>
                <li><i class="fas fa-check-circle"></i> Rispetta gli altri membri dell'equipaggio</li>
                <li><i class="fas fa-times-circle"></i> Niente spam, linguaggio offensivo o contenuti inappropriati</li>
                <li><i class="fas fa-times-circle"></i> Non condividere informazioni personali sensibili</li>
            </ul>
        </div>

        {* Form Actions *}
        <div class="form-actions">
            <button type="submit" class="btn mars-btn primary" id="submit-comment">
                <i class="fas fa-paper-plane"></i>
                <span class="btn-text">Invia Trasmissione</span>
                <div class="btn-loading" style="display: none;">
                    <i class="fas fa-satellite-dish fa-spin"></i>
                    <span>Trasmissione in corso...</span>
                </div>
            </button>
            
            <button type="reset" class="btn btn-secondary" id="reset-form">
                <i class="fas fa-undo"></i>
                Reset Form
            </button>
            
            <div class="form-status" id="form-status" style="display: none;">
                <div class="status-success">
                    <i class="fas fa-check-circle"></i>
                    <span>Trasmissione inviata con successo!</span>
                </div>
                <div class="status-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Errore nell'invio. Controlla i dati e riprova.</span>
                </div>
            </div>
        </div>
    </form>

    {* Formatting Help *}
    <div class="formatting-help">
        <details class="help-accordion">
            <summary class="help-toggle">
                <i class="fas fa-question-circle"></i>
                Guida alla Formattazione
            </summary>
            <div class="help-content">
                <div class="help-grid">
                    <div class="help-item">
                        <strong>Testo in grassetto:</strong>
                        <code>**grassetto**</code>
                    </div>
                    <div class="help-item">
                        <strong>Testo in corsivo:</strong>
                        <code>*corsivo*</code>
                    </div>
                    <div class="help-item">
                        <strong>Link:</strong>
                        <code>[testo](http://link.com)</code>
                    </div>
                    <div class="help-item">
                        <strong>Codice:</strong>
                        <code>`codice`</code>
                    </div>
                </div>
            </div>
        </details>
    </div>
</div>

<style>
/* Mars Comment Form Styles */
.mars-mission-log-form {
    background: rgba(26, 26, 46, 0.8);
    border-radius: var(--border-radius);
    padding: 2rem;
    border: 1px solid rgba(205, 92, 92, 0.3);
    margin-top: 2rem;
    backdrop-filter: blur(5px);
}

.log-form-header {
    text-align: center;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 2px solid rgba(205, 92, 92, 0.3);
}

.log-form-title {
    font-family: var(--font-heading);
    color: var(--mars-orange);
    font-size: 1.8rem;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.log-form-title i {
    color: var(--gold-accent);
    margin-right: 0.5rem;
}

.log-form-description {
    color: var(--starlight);
    opacity: 0.8;
    font-size: 1.1rem;
    margin: 0;
}

/* Form Layout */
.mars-comment-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-label {
    color: var(--starlight);
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-family: var(--font-heading);
}

.form-label.required::after {
    content: '*';
    color: var(--mars-red);
    font-weight: bold;
}

.form-label.optional::after {
    content: '(opzionale)';
    color: var(--mars-dust);
    font-size: 0.8rem;
    font-weight: normal;
}

.form-label i {
    color: var(--mars-orange);
    width: 16px;
}

/* Form Controls */
.form-control {
    width: 100%;
    padding: 0.75rem;
    background: rgba(26, 26, 46, 0.8);
    border: 1px solid rgba(205, 92, 92, 0.3);
    border-radius: var(--border-radius);
    color: var(--starlight);
    font-family: var(--font-body);
    transition: all 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: var(--mars-orange);
    box-shadow: 0 0 0 2px rgba(205, 92, 92, 0.2);
    background: rgba(26, 26, 46, 0.9);
}

.form-control::placeholder {
    color: var(--mars-dust);
    opacity: 0.7;
}

.form-control[readonly] {
    background: rgba(30, 58, 138, 0.2);
    border-color: rgba(30, 58, 138, 0.3);
    cursor: not-allowed;
}

.message-textarea {
    resize: vertical;
    min-height: 120px;
    font-family: var(--font-body);
    line-height: 1.5;
}

/* Field Help */
.field-help {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    color: var(--mars-dust);
    opacity: 0.8;
}

.field-help i {
    color: var(--mars-orange);
}

/* Character Counter */
.character-counter {
    text-align: right;
    font-size: 0.8rem;
    color: var(--mars-dust);
    margin-top: 0.25rem;
}

#char-count {
    color: var(--gold-accent);
    font-weight: 600;
}

.max-chars {
    opacity: 0.7;
}

/* Consent Group */
.consent-group {
    background: rgba(30, 58, 138, 0.2);
    padding: 1rem;
    border-radius: var(--border-radius);
    border: 1px solid rgba(30, 58, 138, 0.3);
}

.consent-checkbox {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

.form-checkbox {
    display: none;
}

.checkbox-label {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    cursor: pointer;
    color: var(--starlight);
    line-height: 1.5;
}

.checkbox-custom {
    width: 20px;
    height: 20px;
    border: 2px solid rgba(205, 92, 92, 0.3);
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(26, 26, 46, 0.8);
    transition: all 0.3s ease;
    flex-shrink: 0;
    margin-top: 2px;
}

.checkbox-custom i {
    color: white;
    font-size: 0.8rem;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.form-checkbox:checked + .checkbox-label .checkbox-custom {
    background: var(--mars-gradient);
    border-color: var(--mars-orange);
}

.form-checkbox:checked + .checkbox-label .checkbox-custom i {
    opacity: 1;
}

.privacy-link {
    color: var(--gold-accent);
    text-decoration: none;
    transition: color 0.3s ease;
}

.privacy-link:hover {
    color: var(--mars-orange);
}

/* Security Group */
.security-group {
    background: rgba(255, 215, 0, 0.1);
    padding: 1rem;
    border-radius: var(--border-radius);
    border: 1px solid rgba(255, 215, 0, 0.2);
}

.security-question p {
    color: var(--gold-accent);
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.security-input {
    max-width: 300px;
}

/* Mission Guidelines */
.mission-guidelines {
    background: rgba(26, 26, 46, 0.7);
    padding: 1.5rem;
    border-radius: var(--border-radius);
    border-left: 4px solid var(--mars-red);
}

.mission-guidelines h4 {
    color: var(--mars-orange);
    font-family: var(--font-heading);
    font-size: 1.1rem;
    margin-bottom: 1rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.mission-guidelines h4 i {
    color: var(--gold-accent);
    margin-right: 0.5rem;
}

.guidelines-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    gap: 0.5rem;
}

.guidelines-list li {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--starlight);
    font-size: 0.9rem;
}

.guidelines-list .fa-check-circle {
    color: #2ed573;
}

.guidelines-list .fa-times-circle {
    color: #ff4757;
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: 1rem;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    padding-top: 1rem;
    border-top: 1px solid rgba(205, 92, 92, 0.3);
}

.mars-btn {
    background: var(--mars-gradient);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: var(--border-radius);
    font-family: var(--font-heading);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: var(--shadow-mars);
}

.mars-btn.primary {
    font-size: 1rem;
    padding: 1rem 2rem;
}

.mars-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 25px rgba(205, 92, 92, 0.4);
}

.mars-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.btn-secondary {
    background: rgba(230, 230, 250, 0.1);
    border: 1px solid rgba(230, 230, 250, 0.2);
    color: var(--starlight);
    padding: 0.75rem 1rem;
    border-radius: var(--border-radius);
    font-family: var(--font-heading);
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-secondary:hover {
    background: rgba(230, 230, 250, 0.2);
    color: var(--starlight);
}

/* Form Status */
.form-status {
    flex-basis: 100%;
    text-align: center;
    padding: 1rem;
    border-radius: var(--border-radius);
}

.status-success {
    color: #2ed573;
    background: rgba(46, 213, 115, 0.1);
    border: 1px solid rgba(46, 213, 115, 0.2);
    padding: 0.75rem;
    border-radius: var(--border-radius);
}

.status-error {
    color: #ff4757;
    background: rgba(255, 71, 87, 0.1);
    border: 1px solid rgba(255, 71, 87, 0.2);
    padding: 0.75rem;
    border-radius: var(--border-radius);
}

/* Formatting Help */
.formatting-help {
    margin-top: 1rem;
}

.help-accordion {
    background: rgba(26, 26, 46, 0.7);
    border-radius: var(--border-radius);
    border: 1px solid rgba(205, 92, 92, 0.3);
}

.help-toggle {
    padding: 1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--mars-orange);
    font-weight: 500;
    list-style: none;
}

.help-toggle::-webkit-details-marker {
    display: none;
}

.help-toggle i {
    color: var(--gold-accent);
}

.help-content {
    padding: 1rem;
    border-top: 1px solid rgba(205, 92, 92, 0.3);
}

.help-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.help-item {
    color: var(--starlight);
    font-size: 0.9rem;
}

.help-item strong {
    color: var(--gold-accent);
}

.help-item code {
    background: rgba(26, 26, 46, 0.8);
    padding: 0.2rem 0.4rem;
    border-radius: 4px;
    color: var(--mars-orange);
    font-family: 'Courier New', monospace;
    border: 1px solid rgba(205, 92, 92, 0.2);
}

/* Responsive */
@media (max-width: 768px) {
    .mars-mission-log-form {
        padding: 1.5rem;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
        align-items: stretch;
    }
    
    .mars-btn,
    .btn-secondary {
        justify-content: center;
        width: 100%;
    }
    
    .help-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Mars Comment Form JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const commentForm = document.getElementById('comment-form');
    const messageTextarea = document.getElementById('comment_content');
    const charCount = document.getElementById('char-count');
    const submitBtn = document.getElementById('submit-comment');
    const formStatus = document.getElementById('form-status');
    
    // Character counter
    if (messageTextarea && charCount) {
        messageTextarea.addEventListener('input', function() {
            const count = this.value.length;
            charCount.textContent = count;
            
            if (count > 1000) {
                charCount.style.color = '#ff4757';
                this.style.borderColor = '#ff4757';
            } else {
                charCount.style.color = 'var(--gold-accent)';
                this.style.borderColor = 'rgba(205, 92, 92, 0.3)';
            }
        });
    }
    
    // Form submission
    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show loading state
            const btnText = submitBtn.querySelector('.btn-text');
            const btnLoading = submitBtn.querySelector('.btn-loading');
            
            btnText.style.display = 'none';
            btnLoading.style.display = 'flex';
            submitBtn.disabled = true;
            
            // Simulate form submission (replace with actual AJAX)
            setTimeout(function() {
                btnText.style.display = 'flex';
                btnLoading.style.display = 'none';
                submitBtn.disabled = false;
                
                // Show success message
                formStatus.style.display = 'block';
                formStatus.querySelector('.status-success').style.display = 'block';
                formStatus.querySelector('.status-error').style.display = 'none';
                
                // Reset form
                commentForm.reset();
                if (charCount) charCount.textContent = '0';
                
                // Hide success message after 5 seconds
                setTimeout(function() {
                    formStatus.style.display = 'none';
                }, 5000);
                
            }, 2000);
        });
    }
});
</script>