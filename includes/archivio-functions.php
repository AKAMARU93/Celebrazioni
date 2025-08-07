<?php
/**
 * Funzioni per l'Archivio Celebrazioni
 * Da includere nel file principale del plugin
 */

// Previeni accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

class ArchivioCelebrazioni {
    
    public function __construct() {
        // Enqueue scripts per la pagina archivio
        add_action('wp_enqueue_scripts', array($this, 'enqueue_archivio_scripts'));
        
        // AJAX handlers
        add_action('wp_ajax_get_celebrazioni_by_date', array($this, 'ajax_get_celebrazioni_by_date'));
        add_action('wp_ajax_nopriv_get_celebrazioni_by_date', array($this, 'ajax_get_celebrazioni_by_date'));
        
        add_action('wp_ajax_get_dates_with_events', array($this, 'ajax_get_dates_with_events'));
        add_action('wp_ajax_nopriv_get_dates_with_events', array($this, 'ajax_get_dates_with_events'));
        
        // Registra template personalizzato
        add_filter('template_include', array($this, 'include_archivio_template'));
        
        // Registra shortcode calendario
        add_shortcode('calendario_celebrazioni', array($this, 'shortcode_calendario_celebrazioni'));
    }
    
    // Enqueue scripts e stili per la pagina archivio
    public function enqueue_archivio_scripts() {
        global $post;
        
        // Controlla se siamo su una pagina con il template archivio o con lo shortcode
        $should_load = false;
        
        if (is_page_template('page-archivio-celebrazioni.php')) {
            $should_load = true;
        } elseif (is_page('archivio-celebrazioni')) {
            $should_load = true;
        } elseif (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'calendario_celebrazioni')) {
            $should_load = true;
        } elseif (is_page() && is_a($post, 'WP_Post')) {
            $template = get_page_template_slug($post->ID);
            if ($template === 'page-archivio-celebrazioni.php') {
                $should_load = true;
            }
        }
        
        if ($should_load) {
            // CSS
            wp_enqueue_style(
                'archivio-celebrazioni-style', 
                CL_PLUGIN_URL . 'assets/archivio-celebrazioni-style.css', 
                array(), 
                '1.0.1'
            );
            
            // JavaScript
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
        }
    }
    
    // Controlla se la pagina ha lo shortcode calendario
    private function has_shortcode_calendario() {
        global $post;
        if (is_a($post, 'WP_Post')) {
            return has_shortcode($post->post_content, 'calendario_celebrazioni');
        }
        return false;
    }
    
    // Template personalizzato per l'archivio
    public function include_archivio_template($template) {
        if (is_page_template('page-archivio-celebrazioni.php')) {
            $plugin_template = CL_PLUGIN_PATH . 'templates/page-archivio-celebrazioni.php';
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }
        return $template;
    }
    
    // AJAX: Ottieni celebrazioni per una data specifica
    public function ajax_get_celebrazioni_by_date() {
        // Verifica nonce
        check_ajax_referer('archivio_celebrazioni_nonce', 'nonce');
        
        $date = sanitize_text_field($_POST['date']);
        
        if (!$date) {
            wp_send_json_error(array('message' => 'Data non valida'));
        }
        
        // Query per ottenere le celebrazioni della data specifica
        $args = array(
            'post_type' => 'celebrazione',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_celebrazione_data',
                    'value' => $date,
                    'compare' => '='
                )
            ),
            'meta_key' => '_celebrazione_ora_inizio',
            'orderby' => 'meta_value',
            'order' => 'ASC'
        );
        
        $celebrazioni = get_posts($args);
        $eventi = array();
        
        foreach ($celebrazioni as $celebrazione) {
            $ora_inizio = get_post_meta($celebrazione->ID, '_celebrazione_ora_inizio', true);
            $ora_fine = get_post_meta($celebrazione->ID, '_celebrazione_ora_fine', true);
            $luogo = get_post_meta($celebrazione->ID, '_celebrazione_luogo', true);
            $tipo = get_post_meta($celebrazione->ID, '_celebrazione_tipo', true);
            
            // Formatta orario
            $orario = '';
            if ($ora_inizio) {
                $orario = $ora_inizio;
                if ($ora_fine) {
                    $orario .= ' - ' . $ora_fine;
                }
            }
            
            // Ottieni descrizione
            $descrizione = $celebrazione->post_excerpt;
            if (empty($descrizione)) {
                $descrizione = wp_trim_words($celebrazione->post_content, 30, '...');
            }
            
            $eventi[] = array(
                'id' => $celebrazione->ID,
                'titolo' => $celebrazione->post_title,
                'orario' => $orario,
                'luogo' => $luogo ?: '',
                'descrizione' => $descrizione,
                'tipo' => $tipo,
                'link' => get_permalink($celebrazione->ID)
            );
        }
        
        wp_send_json_success(array(
            'eventi' => $eventi,
            'count' => count($eventi),
            'date' => $date
        ));
    }
    
    // AJAX: Ottieni tutte le date che hanno eventi
    public function ajax_get_dates_with_events() {
        check_ajax_referer('archivio_celebrazioni_nonce', 'nonce');
        
        global $wpdb;
        
        // Query per ottenere tutte le date uniche con eventi
        $query = "
            SELECT DISTINCT pm.meta_value as data_evento 
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE pm.meta_key = '_celebrazione_data'
            AND p.post_type = 'celebrazione'
            AND p.post_status = 'publish'
            AND pm.meta_value >= %s
            ORDER BY pm.meta_value ASC
        ";
        
        $today = date('Y-m-d');
        $results = $wpdb->get_results($wpdb->prepare($query, $today));
        
        $date_events = array();
        foreach ($results as $result) {
            $date_events[] = $result->data_evento;
        }
        
        wp_send_json_success(array(
            'dates' => $date_events,
            'count' => count($date_events)
        ));
    }
    
    // Shortcode per inserire il calendario in qualsiasi pagina
    public function shortcode_calendario_celebrazioni($atts) {
        $atts = shortcode_atts(array(
            'mese' => date('n'),
            'anno' => date('Y'),
            'mostra_eventi' => true,
            'height' => '500px'
        ), $atts);
        
        // ID univoco per questo shortcode
        $calendar_id = 'calendario-' . uniqid();
        
        ob_start();
        ?>
        <div class="archivio-celebrazioni-shortcode" id="<?php echo $calendar_id; ?>">
            <div class="archivio-content">
                <div class="calendario-section">
                    <div class="calendario-container">
                        <div class="calendario-header">
                            <button class="calendario-nav prev-month">
                                <span>‹</span>
                            </button>
                            <h3 class="calendario-title"></h3>
                            <button class="calendario-nav next-month">
                                <span>›</span>
                            </button>
                        </div>
                        
                        <div class="calendario-grid">
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
                
                <?php if ($atts['mostra_eventi']) : ?>
                <div class="eventi-section">
                    <div class="eventi-container" style="min-height: <?php echo esc_attr($atts['height']); ?>">
                        <div class="eventi-header">
                            <h3 class="eventi-title">Eventi di oggi</h3>
                            <div class="eventi-counter">
                                <span class="events-count">0</span> eventi
                            </div>
                        </div>
                        
                        <div class="eventi-lista">
                            <div class="eventi-loading">
                                <div class="loading-spinner"></div>
                                <p>Caricamento eventi...</p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inizializza il calendario per questo shortcode specifico
            if (typeof ArchivioCelebrazioni !== 'undefined') {
                ArchivioCelebrazioni.initCalendar('<?php echo $calendar_id; ?>');
            }
        });
        </script>
        <?php
        return ob_get_clean();
    }
    
    // Ottieni statistiche eventi per dashboard
    public function get_eventi_stats() {
        global $wpdb;
        
        $stats = array();
        
        // Eventi del mese corrente
        $current_month_start = date('Y-m-01');
        $current_month_end = date('Y-m-t');
        
        $args = array(
            'post_type' => 'celebrazione',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_celebrazione_data',
                    'value' => array($current_month_start, $current_month_end),
                    'compare' => 'BETWEEN',
                    'type' => 'DATE'
                )
            )
        );
        
        $eventi_mese = get_posts($args);
        $stats['eventi_mese'] = count($eventi_mese);
        
        // Eventi futuri
        $args['meta_query'][0] = array(
            'key' => '_celebrazione_data',
            'value' => date('Y-m-d'),
            'compare' => '>=',
            'type' => 'DATE'
        );
        
        $eventi_futuri = get_posts($args);
        $stats['eventi_futuri'] = count($eventi_futuri);
        
        // Eventi per tipo
        $tipi = array('celebrazioni', 'confessioni', 'eucaristia', 'solennita', 'altro');
        $stats['per_tipo'] = array();
        
        foreach ($tipi as $tipo) {
            $args['meta_query'][] = array(
                'key' => '_celebrazione_tipo',
                'value' => $tipo,
                'compare' => '='
            );
            $args['meta_query']['relation'] = 'AND';
            
            $eventi_tipo = get_posts($args);
            $stats['per_tipo'][$tipo] = count($eventi_tipo);
            
            // Rimuovi il meta_query del tipo per la prossima iterazione
            array_pop($args['meta_query']);
        }
        
        return $stats;
    }
    
    // Funzione helper per ottenere eventi di una data specifica
    public static function get_eventi_by_date($date) {
        $args = array(
            'post_type' => 'celebrazione',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_celebrazione_data',
                    'value' => $date,
                    'compare' => '='
                )
            ),
            'meta_key' => '_celebrazione_ora_inizio',
            'orderby' => 'meta_value',
            'order' => 'ASC'
        );
        
        return get_posts($args);
    }
    
    // Funzione helper per ottenere tutte le date con eventi
    public static function get_dates_with_events($from_date = null) {
        global $wpdb;
        
        if (!$from_date) {
            $from_date = date('Y-m-d');
        }
        
        $query = "
            SELECT DISTINCT pm.meta_value as data_evento 
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE pm.meta_key = '_celebrazione_data'
            AND p.post_type = 'celebrazione'
            AND p.post_status = 'publish'
            AND pm.meta_value >= %s
            ORDER BY pm.meta_value ASC
        ";
        
        $results = $wpdb->get_results($wpdb->prepare($query, $from_date));
        
        $dates = array();
        foreach ($results as $result) {
            $dates[] = $result->data_evento;
        }
        
        return $dates;
    }
}

// Inizializza la classe solo se siamo in WordPress e esiste la classe principale
if (class_exists('CelebrazioniLiturgiche')) {
    new ArchivioCelebrazioni();
}