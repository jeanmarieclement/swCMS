<?php
return [
    'name' => 'verso-marte',
    'display_name' => 'Verso Marte',
    'author' => 'Jean-Marie Clément',
    'description' => 'Tema ispirato al pianeta Marte e all\'esplorazione spaziale. Perfetto per il libro "Verso Marte" con colori che richiamano lo spazio profondo e la superficie marziana.',
    'version' => '1.0.0',
    'features' => [
        'responsive_design',
        'space_navigation',
        'mars_color_scheme',
        'stellar_background',
        'planet_animations',
        'article_cards',
        'cosmic_typography',
        'mobile_optimized'
    ],
    'color_scheme' => [
        'primary' => '#CD5C5C',        // Mars red
        'secondary' => '#D2691E',      // Mars orange
        'accent' => '#1e3a8a',         // Deep navy blue
        'background' => '#0B1426',     // Deep space blue
        'surface' => '#1A1A2E',       // Dark space surface
        'text_light' => '#E6E6FA',    // Lavender white
        'text_accent' => '#FFD700'    // Gold for highlights
    ],
    'typography' => [
        'heading_font' => 'Orbitron',  // Futuristic font for headings
        'body_font' => 'Roboto'       // Clean, readable body font
    ],
    'layout' => [
        'type' => 'full_width',
        'max_content_width' => '1200px',
        'sidebar_enabled' => true,
        'header_style' => 'cosmic'
    ]
];