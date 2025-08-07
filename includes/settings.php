<?php
/**
 * Settings.php - Impostazioni Plugin Celebrazioni Liturgiche
 * File autonomo per la gestione delle impostazioni
 */

// Previeni accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

class CelebrazioniLiturgicheSettings {
    
    private $option_name = 'celebrazioni_liturgiche_settings';
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }
    
    // Aggiungi pagina impostazioni al menu
    public function add_settings_page() {
        add_submenu_page(
            'edit.php?post_type=celebrazione',
            'Impostazioni Celebrazioni',
            'Impostazioni',
            'manage_options',
            'celebrazioni-settings',
            array($this, 'render_settings_page')
        );
    }
    
    // Registra le impostazioni
    public function register_settings() {
        register_setting(
            'celebrazioni_liturgiche_settings_group',
            $this->option_name,
            array($this, 'sanitize_settings')
        );
        
        // Sezione Colori Tipi
        add_settings_section(
            'celebrazioni_colors_section',
            'Colori Tipi di Celebrazione',
            array($this, 'colors_section_callback'),
            'celebrazioni-settings'
        );
        
        // Sezione Bottoni
        add_settings_section(
            'celebrazioni_buttons_section',
            'Impostazioni Bottoni',
            array($this, 'buttons_section_callback'),
            'celebrazioni-settings'
        );
        
        // Sezione Layout
        add_settings_section(
            'celebrazioni_layout_section',
            'Impostazioni Layout',
            array($this, 'layout_section_callback'),
            'celebrazioni-settings'
        );
        
        // Campi per i colori
        $this->add_color_fields();
        
        // Campi per i bottoni
        $this->add_button_fields();
        
        // Campi per il layout
        $this->add_layout_fields();
    }
    
    // Aggiungi campi colore
    private function add_color_fields() {
        $types = array(
            'celebrazioni' => 'Celebrazioni',
            'confessioni' => 'Confessioni',
            'eucaristia' => 'Eucaristia',
            'solennita' => 'Solennità',
            'altro' => 'Altro'
        );
        
        foreach ($types as $key => $label) {
            add_settings_field(
                "color_{$key}",
                "Colore {$label}",
                array($this, 'color_field_callback'),
                'celebrazioni-settings',
                'celebrazioni_colors_section',
                array('type' => $key, 'label' => $label)
            );
        }
    }
    
    // Aggiungi campi bottone
    private function add_button_fields() {
        add_settings_field(
            'button_text',
            'Testo Bottone',
            array($this, 'text_field_callback'),
            'celebrazioni-settings',
            'celebrazioni_buttons_section',
            array('field' => 'button_text', 'placeholder' => 'Scopri di più')
        );
        
        add_settings_field(
            'button_color_bg',
            'Colore Sfondo Bottone',
            array($this, 'color_field_callback'),
            'celebrazioni-settings',
            'celebrazioni_buttons_section',
            array('type' => 'button_bg', 'label' => 'Sfondo')
        );
        
        add_settings_field(
            'button_color_text',
            'Colore Testo Bottone',
            array($this, 'color_field_callback'),
            'celebrazioni-settings',
            'celebrazioni_buttons_section',
            array('type' => 'button_text', 'label' => 'Testo')
        );
        
        add_settings_field(
            'button_color_hover',
            'Colore Hover Bottone',
            array($this, 'color_field_callback'),
            'celebrazioni-settings',
            'celebrazioni_buttons_section',
            array('type' => 'button_hover', 'label' => 'Hover')
        );
    }
    
    // Aggiungi campi layout
    private function add_layout_fields() {
        add_settings_field(
            'cards_per_row_desktop',
            'Card per Riga (Desktop)',
            array($this, 'number_field_callback'),
            'celebrazioni-settings',
            'celebrazioni_layout_section',
            array('field' => 'cards_per_row_desktop', 'min' => 1, 'max' => 6, 'default' => 6)
        );
        
        add_settings_field(
            'cards_per_row_tablet',
            'Card per Riga (Tablet)',
            array($this, 'number_field_callback'),
            'celebrazioni-settings',
            'celebrazioni_layout_section',
            array('field' => 'cards_per_row_tablet', 'min' => 1, 'max' => 4, 'default' => 3)
        );
        
        add_settings_field(
            'card_height',
            'Altezza Card (px)',
            array($this, 'number_field_callback'),
            'celebrazioni-settings',
            'celebrazioni_layout_section',
            array('field' => 'card_height', 'min' => 300, 'max' => 600, 'default' => 400)
        );
        
        add_settings_field(
            'show_icons',
            'Mostra Icone nei Label',
            array($this, 'checkbox_field_callback'),
            'celebrazioni-settings',
            'celebrazioni_layout_section',
            array('field' => 'show_icons')
        );
    }
    
    // Callback sezioni
    public function colors_section_callback() {
        echo '<p>Personalizza i colori per ogni tipo di celebrazione. Questi colori verranno applicati ai label e agli accenti delle card.</p>';
    }
    
    public function buttons_section_callback() {
        echo '<p>Personalizza il testo e i colori dei bottoni delle celebrazioni.</p>';
    }
    
    public function layout_section_callback() {
        echo '<p>Personalizza il layout e la disposizione delle card.</p>';
    }
    
    // Callback campi
    public function color_field_callback($args) {
        $options = get_option($this->option_name);
        $type = $args['type'];
        $value = isset($options["color_{$type}"]) ? $options["color_{$type}"] : $this->get_default_color($type);
        
        echo "<input type='color' id='color_{$type}' name='{$this->option_name}[color_{$type}]' value='{$value}' />";
        echo "<span class='color-preview' style='display: inline-block; width: 30px; height: 30px; background: {$value}; border: 1px solid #ddd; margin-left: 10px; vertical-align: middle;'></span>";
    }
    
    public function text_field_callback($args) {
        $options = get_option($this->option_name);
        $field = $args['field'];
        $placeholder = isset($args['placeholder']) ? $args['placeholder'] : '';
        $value = isset($options[$field]) ? $options[$field] : '';
        
        echo "<input type='text' id='{$field}' name='{$this->option_name}[{$field}]' value='{$value}' placeholder='{$placeholder}' class='regular-text' />";
    }
    
    public function number_field_callback($args) {
        $options = get_option($this->option_name);
        $field = $args['field'];
        $min = $args['min'];
        $max = $args['max'];
        $default = $args['default'];
        $value = isset($options[$field]) ? $options[$field] : $default;
        
        echo "<input type='number' id='{$field}' name='{$this->option_name}[{$field}]' value='{$value}' min='{$min}' max='{$max}' class='small-text' />";
    }
    
    public function checkbox_field_callback($args) {
        $options = get_option($this->option_name);
        $field = $args['field'];
        $checked = isset($options[$field]) && $options[$field] ? 'checked' : '';
        
        echo "<input type='checkbox' id='{$field}' name='{$this->option_name}[{$field}]' value='1' {$checked} />";
        echo "<label for='{$field}'>Attiva</label>";
    }
    
    // Colori di default
    private function get_default_color($type) {
        $defaults = array(
            'celebrazioni' => '#667eea',
            'confessioni' => '#d69e2e',
            'eucaristia' => '#38a169',
            'solennita' => '#d53f8c',
            'altro' => '#718096',
            'button_bg' => '#667eea',
            'button_text' => '#ffffff',
            'button_hover' => '#5a67d8'
        );
        
        return isset($defaults[$type]) ? $defaults[$type] : '#667eea';
    }
    
    // Sanitizza le impostazioni
    public function sanitize_settings($input) {
        $sanitized = array();
        
        // Sanitizza colori
        $color_fields = array('celebrazioni', 'confessioni', 'eucaristia', 'solennita', 'altro', 'button_bg', 'button_text', 'button_hover');
        foreach ($color_fields as $field) {
            if (isset($input["color_{$field}"])) {
                $sanitized["color_{$field}"] = sanitize_hex_color($input["color_{$field}"]);
            }
        }
        
        // Sanitizza testo
        if (isset($input['button_text'])) {
            $sanitized['button_text'] = sanitize_text_field($input['button_text']);
        }
        
        // Sanitizza numeri
        $number_fields = array('cards_per_row_desktop', 'cards_per_row_tablet', 'card_height');
        foreach ($number_fields as $field) {
            if (isset($input[$field])) {
                $sanitized[$field] = absint($input[$field]);
            }
        }
        
        // Sanitizza checkbox
        $sanitized['show_icons'] = isset($input['show_icons']) ? 1 : 0;
        
        return $sanitized;
    }
    
    // Enqueue assets admin
    public function enqueue_admin_assets($hook) {
        if ($hook !== 'celebrazione_page_celebrazioni-settings') {
            return;
        }
        
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        
        // CSS personalizzato per la pagina impostazioni
        wp_add_inline_style('wp-color-picker', '
            .celebrazioni-settings-wrap {
                max-width: 1000px;
            }
            .celebrazioni-settings-section {
                background: #fff;
                padding: 20px;
                margin: 20px 0;
                border-radius: 5px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .color-preview {
                border-radius: 3px !important;
            }
            .celebrazioni-preview {
                margin-top: 30px;
                padding: 20px;
                background: #f9f9f9;
                border-radius: 5px;
            }
            .preview-card {
                display: inline-block;
                width: 200px;
                height: 120px;
                margin: 10px;
                border-radius: 10px;
                position: relative;
                vertical-align: top;
            }
            .preview-label {
                position: absolute;
                top: 10px;
                left: 10px;
                padding: 4px 10px;
                border-radius: 15px;
                color: white;
                font-size: 10px;
                font-weight: bold;
            }
            .preview-button {
                position: absolute;
                bottom: 10px;
                left: 50%;
                transform: translateX(-50%);
                padding: 6px 15px;
                border-radius: 20px;
                font-size: 11px;
                font-weight: bold;
                border: none;
                cursor: pointer;
            }
        ');
        
        // JavaScript per preview live
        wp_add_inline_script('wp-color-picker', '
            jQuery(document).ready(function($) {
                $(".wp-color-picker").wpColorPicker({
                    change: function(event, ui) {
                        updatePreview();
                    }
                });
                
                $("input[type=text], input[type=number]").on("input", function() {
                    updatePreview();
                });
                
                function updatePreview() {
                    var types = ["celebrazioni", "confessioni", "eucaristia", "solennita", "altro"];
                    var buttonText = $("#button_text").val() || "Scopri di più";
                    var buttonBg = $("#color_button_bg").val();
                    var buttonTextColor = $("#color_button_text").val();
                    
                    types.forEach(function(type) {
                        var color = $("#color_" + type).val();
                        $(".preview-" + type + " .preview-label").css("background", color);
                    });
                    
                    $(".preview-button").css({
                        "background": buttonBg,
                        "color": buttonTextColor
                    }).text(buttonText);
                }
                
                updatePreview();
            });
        ');
    }
    
    // Render pagina impostazioni
    public function render_settings_page() {
        if (isset($_GET['settings-updated'])) {
            add_settings_error('celebrazioni_messages', 'celebrazioni_message', 'Impostazioni salvate!', 'updated');
        }
        
        settings_errors('celebrazioni_messages');
        ?>
        
        <div class="wrap celebrazioni-settings-wrap">
            <h1>Impostazioni Celebrazioni Liturgiche</h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('celebrazioni_liturgiche_settings_group');
                do_settings_sections('celebrazioni-settings');
                submit_button('Salva Impostazioni');
                ?>
            </form>
            
            <div class="celebrazioni-preview">
                <h3>Anteprima Live</h3>
                <div class="preview-container">
                    <?php
                    $types = array(
                        'celebrazioni' => 'Celebrazioni',
                        'confessioni' => 'Confessioni', 
                        'eucaristia' => 'Eucaristia',
                        'solennita' => 'Solennità',
                        'altro' => 'Altro'
                    );
                    
                    foreach ($types as $key => $label) {
                        echo "<div class='preview-card preview-{$key}' style='background: linear-gradient(135deg, #f0f0f0 0%, #e0e0e0 100%);'>";
                        echo "<div class='preview-label'>{$label}</div>";
                        echo "<button class='preview-button'>Scopri di più</button>";
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>
            
            <div style="margin-top: 30px; padding: 15px; background: #e7f3ff; border-left: 4px solid #2196F3; border-radius: 3px;">
                <h4 style="margin-top: 0;">💡 Come usare le impostazioni:</h4>
                <ul style="margin-bottom: 0;">
                    <li><strong>Colori Tipi:</strong> Ogni tipo di celebrazione avrà il colore scelto per label e accenti</li>
                    <li><strong>Testo Bottone:</strong> Cambia il testo del bottone (es: "Vedi dettagli", "Partecipa", "Info")</li>
                    <li><strong>Colori Bottone:</strong> Personalizza sfondo, testo e colore hover del bottone</li>
                    <li><strong>Layout:</strong> Controlla quante card mostrare per riga e l'altezza delle card</li>
                    <li><strong>Icone:</strong> Abilita/disabilita le icone emoji nei label dei tipi</li>
                </ul>
            </div>
            
            <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 3px;">
                <h4 style="margin-top: 0;">🔄 Dopo aver salvato:</h4>
                <p style="margin-bottom: 0;">Le modifiche saranno applicate automaticamente a tutte le celebrazioni. Potrebbe essere necessario svuotare la cache se usi un plugin di caching.</p>
            </div>
        </div>
        
        <?php
    }
    
    // Metodi pubblici per ottenere le impostazioni
    public static function get_setting($key, $default = '') {
        $options = get_option('celebrazioni_liturgiche_settings', array());
        return isset($options[$key]) ? $options[$key] : $default;
    }
    
    public static function get_type_color($type) {
        $color = self::get_setting("color_{$type}");
        if (empty($color)) {
            $defaults = array(
                'celebrazioni' => '#667eea',
                'confessioni' => '#d69e2e',
                'eucaristia' => '#38a169',
                'solennita' => '#d53f8c',
                'altro' => '#718096'
            );
            $color = isset($defaults[$type]) ? $defaults[$type] : '#667eea';
        }
        return $color;
    }
    
    public static function get_button_text() {
        return self::get_setting('button_text', 'Scopri di più');
    }
    
    public static function get_button_colors() {
        return array(
            'bg' => self::get_setting('color_button_bg', '#667eea'),
            'text' => self::get_setting('color_button_text', '#ffffff'),
            'hover' => self::get_setting('color_button_hover', '#5a67d8')
        );
    }
}

// Inizializza le impostazioni
new CelebrazioniLiturgicheSettings();