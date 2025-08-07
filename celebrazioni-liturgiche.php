<?php
/**
 * Plugin Name: Celebrazioni Liturgiche
 * Description: Plugin per gestire e visualizzare le celebrazioni liturgiche
 * Version: 1.0.2
 * Author: Ivan Zara
 * License: GPL v2 or later
 */

if (!defined('ABSPATH')) exit;

define('CL_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CL_PLUGIN_PATH', plugin_dir_path(__FILE__));

class CelebrazioniLiturgiche {
    
    public function __construct() {
        register_activation_hook(__FILE__, array($this, 'attiva_plugin'));
        add_action('init', array($this, 'registra_cpt_celebrazioni'));
        add_action('add_meta_boxes', array($this, 'aggiungi_metabox'));
        add_action('save_post', array($this, 'salva_metabox'));
        add_shortcode('celebrazioni_liturgiche', array($this, 'shortcode_celebrazioni'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_filter('manage_celebrazione_posts_columns', array($this, 'set_custom_columns'));
        add_action('manage_celebrazione_posts_custom_column', array($this, 'custom_column_content'), 10, 2);
        add_action('wp_ajax_load_more_celebrations', array($this, 'ajax_load_more'));
        add_action('wp_ajax_nopriv_load_more_celebrations', array($this, 'ajax_load_more'));
        add_filter('template_include', array($this, 'include_celebrazione_template'));
        add_filter('theme_page_templates', array($this, 'registra_template_archivio'));
        
        // Includi file aggiuntivi se esistono
        $files = ['includes/settings.php', 'includes/archivio-functions.php'];
        foreach ($files as $file) {
            $path = CL_PLUGIN_PATH . $file;
            if (file_exists($path)) require_once $path;
        }
    }
    
    public function attiva_plugin() {
        $this->registra_cpt_celebrazioni();
        flush_rewrite_rules();
        $this->crea_pagina_archivio();
    }
    
    public function registra_template_archivio($templates) {
        $templates['page-archivio-celebrazioni.php'] = 'Archivio Celebrazioni';
        return $templates;
    }
    
    public function crea_pagina_archivio() {
        if (!get_page_by_title('Archivio Celebrazioni')) {
            wp_insert_post(array(
                'post_title' => 'Archivio Celebrazioni',
                'post_content' => '',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_name' => 'archivio-celebrazioni',
                'page_template' => 'page-archivio-celebrazioni.php'
            ));
        }
    }
    
    public function registra_cpt_celebrazioni() {
        register_post_type('celebrazione', array(
            'labels' => array(
                'name' => 'Celebrazioni',
                'singular_name' => 'Celebrazione',
                'menu_name' => 'Celebrazioni Liturgiche',
                'add_new' => 'Aggiungi Nuova',
                'add_new_item' => 'Aggiungi Nuova Celebrazione',
                'edit_item' => 'Modifica Celebrazione',
                'new_item' => 'Nuova Celebrazione',
                'view_item' => 'Visualizza Celebrazione',
                'all_items' => 'Tutte le Celebrazioni',
                'search_items' => 'Cerca Celebrazioni',
                'not_found' => 'Nessuna celebrazione trovata',
                'not_found_in_trash' => 'Nessuna celebrazione nel cestino',
            ),
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'celebrazione'),
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => 5,
            'menu_icon' => 'dashicons-calendar-alt',
            'supports' => array('title', 'editor', 'thumbnail'),
        ));
    }
    
    public function aggiungi_metabox() {
        add_meta_box('celebrazione_dettagli', 'Dettagli Celebrazione', 
                    array($this, 'render_metabox'), 'celebrazione', 'normal', 'high');
    }
    
    public function render_metabox($post) {
        wp_nonce_field('celebrazione_metabox', 'celebrazione_metabox_nonce');
        
        $fields = array(
            'data' => get_post_meta($post->ID, '_celebrazione_data', true),
            'ora_inizio' => get_post_meta($post->ID, '_celebrazione_ora_inizio', true),
            'ora_fine' => get_post_meta($post->ID, '_celebrazione_ora_fine', true),
            'tipo' => get_post_meta($post->ID, '_celebrazione_tipo', true),
            'link' => get_post_meta($post->ID, '_celebrazione_link', true),
            'luogo' => get_post_meta($post->ID, '_celebrazione_luogo', true),
            'indirizzo' => get_post_meta($post->ID, '_celebrazione_indirizzo', true),
            'telefono' => get_post_meta($post->ID, '_celebrazione_telefono', true),
            'email' => get_post_meta($post->ID, '_celebrazione_email', true),
            'sito_web' => get_post_meta($post->ID, '_celebrazione_sito_web', true),
        );
        ?>
        <table class="form-table">
            <tr>
                <th><label for="celebrazione_data">Data Celebrazione</label></th>
                <td><input type="date" id="celebrazione_data" name="celebrazione_data" value="<?php echo esc_attr($fields['data']); ?>" required class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="celebrazione_tipo">Tipo Celebrazione</label></th>
                <td>
                    <select id="celebrazione_tipo" name="celebrazione_tipo" class="regular-text">
                        <option value="celebrazioni" <?php selected($fields['tipo'], 'celebrazioni'); ?>>Celebrazioni</option>
                        <option value="confessioni" <?php selected($fields['tipo'], 'confessioni'); ?>>Orario Confessioni</option>
                        <option value="eucaristia" <?php selected($fields['tipo'], 'eucaristia'); ?>>Eucaristia</option>
                        <option value="solennita" <?php selected($fields['tipo'], 'solennita'); ?>>Solennità</option>
                        <option value="altro" <?php selected($fields['tipo'], 'altro'); ?>>Altro</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="celebrazione_ora_inizio">Ora Inizio</label></th>
                <td><input type="time" id="celebrazione_ora_inizio" name="celebrazione_ora_inizio" value="<?php echo esc_attr($fields['ora_inizio']); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="celebrazione_ora_fine">Ora Fine</label></th>
                <td><input type="time" id="celebrazione_ora_fine" name="celebrazione_ora_fine" value="<?php echo esc_attr($fields['ora_fine']); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="celebrazione_link">Link Dettagli</label></th>
                <td><input type="url" id="celebrazione_link" name="celebrazione_link" value="<?php echo esc_attr($fields['link']); ?>" placeholder="https://..." class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="celebrazione_luogo">Nome Luogo</label></th>
                <td><input type="text" id="celebrazione_luogo" name="celebrazione_luogo" value="<?php echo esc_attr($fields['luogo']); ?>" placeholder="es: Duomo di Milano" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="celebrazione_indirizzo">Indirizzo Completo</label></th>
                <td><textarea id="celebrazione_indirizzo" name="celebrazione_indirizzo" rows="3" class="large-text" placeholder="es: Piazza del Duomo, 20122 Milano MI"><?php echo esc_textarea($fields['indirizzo']); ?></textarea></td>
            </tr>
            <tr>
                <th><label for="celebrazione_telefono">Telefono</label></th>
                <td><input type="tel" id="celebrazione_telefono" name="celebrazione_telefono" value="<?php echo esc_attr($fields['telefono']); ?>" placeholder="es: +39 02 1234567" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="celebrazione_email">Email</label></th>
                <td><input type="email" id="celebrazione_email" name="celebrazione_email" value="<?php echo esc_attr($fields['email']); ?>" placeholder="es: info@duomomilano.it" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="celebrazione_sito_web">Sito Web</label></th>
                <td><input type="url" id="celebrazione_sito_web" name="celebrazione_sito_web" value="<?php echo esc_attr($fields['sito_web']); ?>" placeholder="es: https://www.duomomilano.it" class="regular-text" /></td>
            </tr>
        </table>
        <?php
    }
    
    public function salva_metabox($post_id) {
        if (!isset($_POST['celebrazione_metabox_nonce']) || 
            !wp_verify_nonce($_POST['celebrazione_metabox_nonce'], 'celebrazione_metabox') ||
            defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ||
            !current_user_can('edit_post', $post_id)) {
            return;
        }
        
        $fields = array(
            'celebrazione_data' => 'sanitize_text_field',
            'celebrazione_ora_inizio' => 'sanitize_text_field',
            'celebrazione_ora_fine' => 'sanitize_text_field',
            'celebrazione_tipo' => 'sanitize_text_field',
            'celebrazione_link' => 'esc_url_raw',
            'celebrazione_luogo' => 'sanitize_text_field',
            'celebrazione_indirizzo' => 'sanitize_textarea_field',
            'celebrazione_telefono' => 'sanitize_text_field',
            'celebrazione_email' => 'sanitize_email',
            'celebrazione_sito_web' => 'esc_url_raw',
        );
        
        foreach ($fields as $field => $sanitize_func) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, '_' . $field, $sanitize_func($_POST[$field]));
            }
        }
    }
    
    public function set_custom_columns($columns) {
        return array(
            'cb' => $columns['cb'],
            'title' => 'Titolo',
            'data' => 'Data',
            'orario' => 'Orario',
            'tipo' => 'Tipo',
            'date' => 'Data Pubblicazione'
        );
    }
    
    public function custom_column_content($column, $post_id) {
        switch ($column) {
            case 'data':
                $data = get_post_meta($post_id, '_celebrazione_data', true);
                if ($data) echo date_i18n('d F Y', strtotime($data));
                break;
            case 'orario':
                $ora_inizio = get_post_meta($post_id, '_celebrazione_ora_inizio', true);
                $ora_fine = get_post_meta($post_id, '_celebrazione_ora_fine', true);
                if ($ora_inizio && $ora_fine) {
                    echo $ora_inizio . ' - ' . $ora_fine;
                } elseif ($ora_inizio) {
                    echo $ora_inizio;
                }
                break;
            case 'tipo':
                $tipo = get_post_meta($post_id, '_celebrazione_tipo', true);
                echo ucfirst($tipo);
                break;
        }
    }
    
    public function enqueue_scripts() {
        wp_enqueue_style('celebrazioni-style', CL_PLUGIN_URL . 'assets/style.css', array(), '1.0.3');
        wp_enqueue_script('celebrazioni-script', CL_PLUGIN_URL . 'assets/script.js', array('jquery'), '1.0.3', true);
        
        if (is_singular('celebrazione')) {
            wp_enqueue_style('single-celebrazione-style', CL_PLUGIN_URL . 'assets/single-celebrazione.css', array(), '1.0.1');
        }
        
        if (is_page_template('page-archivio-celebrazioni.php') || is_page('archivio-celebrazioni')) {
            wp_enqueue_style('archivio-celebrazioni-style', CL_PLUGIN_URL . 'assets/archivio-celebrazioni-style.css', array(), '1.0.1');
        }
        
        wp_localize_script('celebrazioni-script', 'celebrazioni_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('celebrazioni_nonce')
        ));
    }
    
    public function admin_enqueue_scripts($hook) {
        global $post_type;
        if ('celebrazione' === $post_type) {
            wp_enqueue_media();
        }
    }
    
    public function include_celebrazione_template($template) {
        if (is_singular('celebrazione')) {
            $plugin_template = CL_PLUGIN_PATH . 'templates/single-celebrazione.php';
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }
        
        if (is_page_template('page-archivio-celebrazioni.php')) {
            $plugin_template = CL_PLUGIN_PATH . 'templates/page-archivio-celebrazioni.php';
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }
        
        return $template;
    }
    
    public function shortcode_celebrazioni($atts) {
        $atts = shortcode_atts(array(
            'numero' => 6,
            'tipo' => '',
            'mostra_passate' => false
        ), $atts);
        
        $args = array(
            'post_type' => 'celebrazione',
            'posts_per_page' => intval($atts['numero']),
            'meta_key' => '_celebrazione_data',
            'orderby' => 'meta_value',
            'order' => 'ASC',
            'post_status' => 'publish'
        );
        
        if (!$atts['mostra_passate']) {
            $args['meta_query'] = array(array(
                'key' => '_celebrazione_data',
                'value' => date('Y-m-d'),
                'compare' => '>=',
                'type' => 'DATE'
            ));
        }
        
        if (!empty($atts['tipo'])) {
            if (!isset($args['meta_query'])) $args['meta_query'] = array();
            $args['meta_query']['relation'] = 'AND';
            $args['meta_query'][] = array(
                'key' => '_celebrazione_tipo',
                'value' => sanitize_text_field($atts['tipo']),
                'compare' => '='
            );
        }
        
        $celebrazioni = new WP_Query($args);
        
        ob_start();
        
        if ($celebrazioni->have_posts()) : ?>
            <div class="celebrazioni-container">
                <div class="celebrazioni-header">
                    <h2>Prossime Celebrazioni Liturgiche</h2>
                    <?php if ($celebrazioni->found_posts > 6) : ?>
                        <a href="<?php echo get_permalink(get_page_by_path('archivio-celebrazioni')); ?>" class="link-tutte">TUTTE LE CELEBRAZIONI</a>
                    <?php endif; ?>
                </div>
                
                <div class="celebrazioni-carousel">
                    <div class="celebrazioni-wrapper">
                        <?php while ($celebrazioni->have_posts()) : $celebrazioni->the_post(); 
                            $this->render_celebrazione_card();
                        endwhile; ?>
                    </div>
                </div>
                
                <div class="celebrazioni-dots"></div>
            </div>
        <?php else : ?>
            <div class="celebrazioni-container">
                <div class="celebrazioni-no-content">
                    <h3>Nessuna celebrazione programmata</h3>
                    <p>Al momento non ci sono celebrazioni liturgiche programmate per le prossime date.</p>
                </div>
            </div>
        <?php endif;
        
        wp_reset_postdata();
        return ob_get_clean();
    }
    
    private function render_celebrazione_card() {
        $data = get_post_meta(get_the_ID(), '_celebrazione_data', true);
        $ora_inizio = get_post_meta(get_the_ID(), '_celebrazione_ora_inizio', true);
        $ora_fine = get_post_meta(get_the_ID(), '_celebrazione_ora_fine', true);
        $tipo = get_post_meta(get_the_ID(), '_celebrazione_tipo', true);
        $link = get_post_meta(get_the_ID(), '_celebrazione_link', true);
        
        if (empty($data)) return;
        
        $timestamp = strtotime($data);
        $giorno_settimana = date_i18n('l', $timestamp);
        $giorno = date('d', $timestamp);
        $mese = date_i18n('F', $timestamp);
        
        $tipo_config = array(
            'confessioni' => array('label' => 'Confessioni', 'icon' => '🙏'),
            'eucaristia' => array('label' => 'Eucaristia', 'icon' => '⛪'),
            'solennita' => array('label' => 'Solennità', 'icon' => '✨'),
            'celebrazioni' => array('label' => 'Celebrazioni', 'icon' => '✟'),
            'altro' => array('label' => 'Altro', 'icon' => '📅')
        );
        
        $config = isset($tipo_config[$tipo]) ? $tipo_config[$tipo] : $tipo_config['celebrazioni'];
        ?>
        
        <div class="celebrazione-card" data-tipo="<?php echo esc_attr($tipo); ?>">
            
            <?php if (has_post_thumbnail()) : ?>
                <div class="celebrazione-image">
                    <?php the_post_thumbnail('large'); ?>
                </div>
            <?php else : ?>
                <div class="celebrazione-image celebrazione-default-bg"></div>
            <?php endif; ?>
            
            <div class="celebrazione-content">
                <div class="celebrazione-label">
                    <?php echo $config['icon'] . ' ' . $config['label']; ?>
                </div>
                
                <div class="celebrazione-date-section">
                    <div class="celebrazione-giorno"><?php echo strtoupper($giorno_settimana); ?></div>
                    <div class="celebrazione-data-grande"><?php echo $giorno; ?></div>
                    <div class="celebrazione-mese"><?php echo strtoupper($mese); ?></div>
                </div>
                
                <?php if ($ora_inizio || $ora_fine) : ?>
                <div class="celebrazione-orario">
                    <?php 
                    echo '🕐 ';
                    if ($ora_inizio && $ora_fine) {
                        echo $ora_inizio . ' - ' . $ora_fine;
                    } elseif ($ora_inizio) {
                        echo $ora_inizio;
                    }
                    ?>
                </div>
                <?php endif; ?>
                
                <h3 class="celebrazione-titolo"><?php the_title(); ?></h3>
                
                <?php if ($link) : ?>
                    <a href="<?php echo esc_url($link); ?>" class="celebrazione-btn">Scopri di più</a>
                <?php else : ?>
                    <a href="<?php the_permalink(); ?>" class="celebrazione-btn">Scopri di più</a>
                <?php endif; ?>
            </div>
        </div>
        
        <?php
    }
    
    public function ajax_load_more() {
        check_ajax_referer('celebrazioni_nonce', 'nonce');
        
        $page = isset($_POST['page']) ? intval($_POST['page']) : 2;
        
        $args = array(
            'post_type' => 'celebrazione',
            'posts_per_page' => 6,
            'paged' => $page,
            'meta_key' => '_celebrazione_data',
            'orderby' => 'meta_value',
            'order' => 'ASC',
            'post_status' => 'publish',
            'meta_query' => array(array(
                'key' => '_celebrazione_data',
                'value' => date('Y-m-d'),
                'compare' => '>=',
                'type' => 'DATE'
            ))
        );
        
        $celebrazioni = new WP_Query($args);
        
        ob_start();
        
        if ($celebrazioni->have_posts()) {
            while ($celebrazioni->have_posts()) : $celebrazioni->the_post();
                $this->render_celebrazione_card();
            endwhile;
        }
        
        $html = ob_get_clean();
        $has_more = $celebrazioni->max_num_pages > $page;
        
        wp_send_json_success(array(
            'html' => $html,
            'has_more' => $has_more,
            'found_posts' => $celebrazioni->found_posts,
            'page' => $page
        ));
        
        wp_die();
    }
}

// Inizializza il plugin
new CelebrazioniLiturgiche();