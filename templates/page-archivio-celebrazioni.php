<?php
/**
 * Template Name: Archivio Celebrazioni
 * 
 * Pagina per visualizzare calendario e eventi delle celebrazioni
 */

get_header(); 

// Forza il caricamento degli stili
wp_enqueue_style(
    'archivio-celebrazioni-style', 
    CL_PLUGIN_URL . 'assets/archivio-celebrazioni-style.css', 
    array(), 
    '1.0.1'
);

wp_enqueue_script(
    'archivio-celebrazioni-script', 
    CL_PLUGIN_URL . 'assets/archivio-celebrazioni-script.js', 
    array('jquery'), 
    '1.0.1', 
    true
);

// Localizza script per AJAX
wp_localize_script('archivio-celebrazioni-script', 'archivio_vars', array(
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('archivio_celebrazioni_nonce'),
    'current_date' => date('Y-m-d'),
    'strings' => array(
        'loading' => 'Caricamento eventi...',
        'no_events' => 'Nessun evento programmato',
        'events_of' => 'Eventi del',
        'error' => 'Errore nel caricamento degli eventi'
    )
));
?>

<div class="archivio-celebrazioni-wrapper">
    
    <!-- Header della pagina -->
    <div class="archivio-header">
        <div class="container">
            <h1 class="archivio-title">Archivio: <em>Celebrazioni</em></h1>
        </div>
    </div>

    <!-- Contenuto principale -->
    <div class="archivio-main">
        <div class="container">
            <div class="archivio-layout">
                
                <!-- Calendario a sinistra -->
                <div class="archivio-calendario-column">
                    <div class="calendario-container">
                        <div class="calendario-header">
                            <button id="prevMonth" class="calendario-nav prev-month">
                                <span>‹</span>
                            </button>
                            <h3 id="currentMonth" class="calendario-title"></h3>
                            <button id="nextMonth" class="calendario-nav next-month">
                                <span>›</span>
                            </button>
                        </div>
                        
                        <div id="calendario" class="calendario-grid">
                            <!-- Il calendario verrà generato da JavaScript -->
                        </div>
                        
                        <div class="calendario-legenda">
                            <div class="legenda-item">
                                <span class="legenda-dot con-eventi"></span>
                                <span class="legenda-text">Giorni con eventi</span>
                            </div>
                            <div class="legenda-item">
                                <span class="legenda-dot oggi"></span>
                                <span class="legenda-text">Oggi</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Eventi a destra -->
                <div class="archivio-eventi-column">
                    <div class="eventi-container">
                        <div class="eventi-header">
                            <h2 id="selectedDate">Eventi di oggi</h2>
                            <div class="eventi-counter">
                                <span id="eventsCount">0</span> eventi
                            </div>
                        </div>
                        
                        <div id="eventiLista" class="eventi-lista">
                            <div class="eventi-loading">
                                <div class="loading-spinner"></div>
                                <p>Caricamento eventi...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>