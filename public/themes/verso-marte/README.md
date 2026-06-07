# Tema "Verso Marte" per swCMS

Un tema ispirato all'esplorazione spaziale e al pianeta Marte, perfetto per il libro "Verso Marte".

## 🚀 Caratteristiche

- **Design Spaziale**: Colori ispirati a Marte e allo spazio profondo
- **Animazioni**: Effetti di particelle, stelle e animazioni orbitali
- **Responsive**: Ottimizzato per tutti i dispositivi
- **Tipografia**: Font Orbitron per i titoli e Roboto per il testo
- **Tema Scuro**: Perfetto per l'atmosfera spaziale
- **Interazioni**: Effetti hover e animazioni fluide

## 🎨 Palette Colori

- **Mars Red**: `#CD5C5C` - Colore primario
- **Mars Orange**: `#D2691E` - Colore secondario  
- **Deep Space**: `#0B1426` - Sfondo principale
- **Deep Navy**: `#1e3a8a` - Accenti
- **Gold**: `#FFD700` - Highlights
- **Starlight**: `#E6E6FA` - Testo

## 📁 Struttura File

```
verso-marte/
├── theme.conf.php           # Configurazione tema
├── css/
│   └── style.css           # Stili principali
├── js/
│   └── mars-theme.js       # Interazioni JavaScript
├── templates/
│   ├── layout.tpl          # Layout base
│   ├── home.tpl            # Pagina home
│   ├── article.tpl         # Articoli
│   ├── page.tpl            # Pagine
│   ├── 404.tpl             # Errore 404
│   └── partials/
│       ├── header.tpl      # Header
│       ├── footer.tpl      # Footer
│       ├── comments_list.tpl # Lista commenti
│       └── comment_form.tpl  # Form commenti
├── img/                    # Immagini tema (vuota)

```

## 🛠️ Installazione

1. Il tema è già installato in `/public/themes/verso-marte/`
2. Accedi al pannello admin di swCMS
3. Vai su **Temi** nel menu
4. Seleziona "Verso Marte" e clicca "Attiva"



## 🎭 Personalizzazioni

### Modificare i Colori

Modifica le variabili CSS in `css/style.css`:

```css
:root {
    --mars-red: #CD5C5C;
    --mars-orange: #D2691E;
    --deep-space: #0B1426;
    /* ... altri colori */
}
```

### Aggiungere Animazioni

Il tema include già diverse animazioni:
- Stelle tremolanti nel background
- Particelle fluttuanti
- Rotazione del pianeta Marte
- Effetti hover sui bottoni

### Modificare la Navigazione

Personalizza il menu in `templates/partials/header.tpl` o configura tramite il pannello admin.

## 🌟 Funzionalità Speciali

### Terminologia Marziana
- **Sol**: Giorno marziano (invece di "giorno")
- **Base Terra**: Home page
- **Missioni**: Articoli/Post
- **Equipaggio**: Team/Autori
- **Mission Control**: Pannello admin
- **Trasmissioni**: Commenti

### Widget Sidebar
- **Mission Control**: Status della missione
- **Meteo su Marte**: Condizioni atmosferiche simulate
- **Ultime Trasmissioni**: Articoli recenti

### Pagina 404
Design speciale con:
- Satellite perso nello spazio
- Detriti fluttuanti
- Opzioni di "salvataggio"
- Curiosità su Marte

## 💡 Suggerimenti

1. **Contenuti**: Utilizza terminologia spaziale nei tuoi articoli
2. **Immagini**: Aggiungi foto di Marte e dello spazio nella directory `img/`
3. **Font**: I font Google Fonts si caricano automaticamente
4. **Mobile**: Il tema è completamente responsive

## 🔧 Supporto Browser

- ✅ Chrome (consigliato)
- ✅ Firefox
- ✅ Safari  
- ✅ Edge
- ✅ Mobile browsers

## 📝 Note Tecniche

- **Framework CSS**: Custom CSS con variabili
- **JavaScript**: Vanilla JS con jQuery
- **Font**: Google Fonts (Orbitron + Roboto)
- **Icone**: Font Awesome 6.4.0
- **Compatibilità**: swCMS 1.0+

## 🚀 Per il Futuro

Possibili miglioramenti:
- [ ] Integrazione API NASA per meteo Marte reale
- [ ] Più animazioni spaziali
- [ ] Modalità scura/chiara
- [ ] Suoni spaziali (opzionali)
- [ ] Timeline missioni interattiva

---

**Buon viaggio verso Marte!** 🔴✨