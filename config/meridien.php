<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Racine des polycopiés
    |--------------------------------------------------------------------------
    | Chemin relatif à public/. Les fichiers y sont lus en place : rien n'est
    | copié ni déplacé, la bibliothèque pointe directement dessus.
    */
    'pdf_root' => env('MERIDIEN_PDF_ROOT', 'pdfs'),

    /*
    |--------------------------------------------------------------------------
    | Binaires Poppler / MiKTeX
    |--------------------------------------------------------------------------
    | Utilisés à l'ingestion pour extraire le texte et compter les pages.
    | Laisser null pour s'en remettre au PATH du système.
    */
    'pdftotext' => env('MERIDIEN_PDFTOTEXT', 'pdftotext'),
    'pdfinfo' => env('MERIDIEN_PDFINFO', 'pdfinfo'),

    /*
    |--------------------------------------------------------------------------
    | Seuil de détection des scans
    |--------------------------------------------------------------------------
    | En dessous de ce nombre de caractères extraits, le document est considéré
    | comme une image numérisée : les copies d'examen manuscrites tombent ici.
    */
    'scan_threshold' => 200,

    /*
    |--------------------------------------------------------------------------
    | Répétition espacée (SM-2)
    |--------------------------------------------------------------------------
    | Intervalles resserrés par rapport au SM-2 canonique : l'échéance est à
    | 20 jours, on ne peut pas se permettre des intervalles de plusieurs mois.
    */
    'sm2' => [
        'min_ease' => 1.3,
        'first_interval' => 1,
        'second_interval' => 3,
        'max_interval' => 10,     // plafonné : au-delà, la carte sortirait de la fenêtre de révision
        'mature_after' => 3,      // répétitions réussies avant de considérer la carte acquise
    ],

    /*
    |--------------------------------------------------------------------------
    | Moteur de planning
    |--------------------------------------------------------------------------
    */
    'planning' => [
        'block_minutes' => 45,        // granularité d'un bloc de travail
        'break_minutes' => 10,
        'max_blocks_per_day' => 10,
        'reserve_last_day' => true,   // la veille de chaque épreuve est réservée à sa révision
    ],

];