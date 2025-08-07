<?php
/**
 * Template per la visualizzazione singola di una celebrazione
 * File: single-celebrazione.php
 * Layout: Informazioni a sinistra, Foto a destra
 */

get_header(); ?>

<div class="celebrazione-single-wrapper">
    <?php while (have_posts()) : the_post(); 
        $data = get_post_meta(get_the_ID(), '_celebrazione_data', true);
        $ora_inizio = get_post_meta(get_the_ID(), '_celebrazione_ora_inizio', true);
        $ora_fine = get_post_meta(get_the_ID(), '_celebrazione_ora_fine', true);
        $tipo = get_post_meta(get_the_ID(), '_celebrazione_tipo', true);
        $link_dettagli = get_post_meta(get_the_ID(), '_celebrazione_link', true);
        
        // Nuovi campi contatti
        $luogo = get_post_meta(get_the_ID(), '_celebrazione_luogo', true);
        $indirizzo = get_post_meta(get_the_ID(), '_celebrazione_indirizzo', true);
        $telefono = get_post_meta(get_the_ID(), '_celebrazione_telefono', true);
        $email = get_post_meta(get_the_ID(), '_celebrazione_email', true);
        $sito_web = get_post_meta(get_the_ID(), '_celebrazione_sito_web', true);
        
        // Formatta data
        $timestamp = strtotime($data);
        $giorno_settimana = date_i18n('l', $timestamp);
        $giorno = date('d', $timestamp);
        $mese = date_i18n('F', $timestamp);
        $anno = date('Y', $timestamp);
        $giorno_completo = date_i18n('l d F Y', $timestamp);
        
        // Determina il tipo di celebrazione con icona
        $tipo_config = array(
            'confessioni' => array('label' => 'Confessioni', 'icon' => '🙏', 'color' => '#d69e2e'),
            'eucaristia' => array('label' => 'Eucaristia', 'icon' => '⛪', 'color' => '#38a169'),
            'solennita' => array('label' => 'Solennità', 'icon' => '✨', 'color' => '#d53f8c'),
            'celebrazioni' => array('label' => 'Celebrazioni', 'icon' => '✟', 'color' => '#667eea'),
            'altro' => array('label' => 'Altro', 'icon' => '📅', 'color' => '#718096')
        );
        
        $config = isset($tipo_config[$tipo]) ? $tipo_config[$tipo] : $tipo_config['celebrazioni'];
    ?>
    
    <!-- Hero Section con Titolo Visibile -->
    <div class="celebrazione-hero-section">
        <div class="hero-background">
            <?php if (has_post_thumbnail()) : ?>
                <?php the_post_thumbnail('full', array('class' => 'hero-image')); ?>
            <?php endif; ?>
            <div class="hero-overlay"></div>
        </div>
        
        <div class="hero-content">
            <div class="container">
                <div class="hero-info">
                    <!-- Titolo prominente -->
                    <h1 class="hero-title"><?php the_title(); ?></h1>
                    
                    <!-- Meta informazioni -->
                    <div class="hero-meta">
                        <div class="meta-item">
                            <span class="meta-icon">📅</span>
                            <span class="meta-text">Sabato <?php echo date('d', $timestamp); ?> <?php echo $mese; ?> <?php echo $anno; ?></span>
                        </div>
                        <?php if ($ora_inizio) : ?>
                        <div class="meta-item">
                            <span class="meta-icon">🕐</span>
                            <span class="meta-text"><?php echo $ora_inizio; ?><?php echo $ora_fine ? ' - ' . $ora_fine : ''; ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="meta-item">
                            <span class="meta-icon"><?php echo $config['icon']; ?></span>
                            <span class="meta-text"><?php echo $config['label']; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Content Section -->
    <div class="celebrazione-main-section">
        <div class="container">
            <div class="celebrazione-layout">
                
                <!-- Left Column - Informazioni -->
                <div class="celebrazione-info-column">
                    
                    <!-- Header con data e tipo (semplificato) -->
                    <div class="celebrazione-header">
                        <div class="celebrazione-date-display">
                            <div class="date-number"><?php echo $giorno; ?></div>
                            <div class="date-details">
                                <div class="date-month"><?php echo strtoupper($mese); ?></div>
                                <div class="date-year"><?php echo $anno; ?></div>
                                <div class="date-day"><?php echo strtoupper($giorno_settimana); ?></div>
                            </div>
                        </div>
                        
                        <div class="celebrazione-type-badge" style="background: <?php echo $config['color']; ?>">
                            <span class="type-icon"><?php echo $config['icon']; ?></span>
                            <span class="type-label"><?php echo $config['label']; ?></span>
                        </div>
                    </div>
                    
                    <!-- Informazioni rapide -->
                    <div class="celebrazione-quick-info">
                        <?php if ($ora_inizio) : ?>
                        <div class="info-item">
                            <span class="info-icon">🕐</span>
                            <div class="info-content">
                                <span class="info-label">Orario</span>
                                <span class="info-value">
                                    <?php 
                                    if ($ora_inizio && $ora_fine) {
                                        echo $ora_inizio . ' - ' . $ora_fine;
                                    } else {
                                        echo $ora_inizio;
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="info-item">
                            <span class="info-icon">📍</span>
                            <div class="info-content">
                                <span class="info-label">Luogo</span>
                                <span class="info-value"><?php echo $luogo ? esc_html($luogo) : 'Da definire'; ?></span>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-icon">📅</span>
                            <div class="info-content">
                                <span class="info-label">Data completa</span>
                                <span class="info-value"><?php echo $giorno_completo; ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contenuto principale -->
                    <div class="celebrazione-content-text">
                        <?php 
                        $content = get_the_content();
                        if (empty(trim(strip_tags($content)))) {
                            echo '<p>Partecipa a questa importante celebrazione liturgica. Ti aspettiamo per condividere insieme questo momento di fede e comunità.</p>';
                            echo '<p>Per ulteriori informazioni, contatta la segreteria parrocchiale.</p>';
                        } else {
                            the_content();
                        }
                        ?>
                    </div>
                    
                    <!-- Bottoni azione -->
                    <div class="celebrazione-actions">
                        <button class="action-btn primary-btn" onclick="addToCalendar()">
                            <span class="btn-icon">📅</span>
                            <span>Aggiungi al Calendario</span>
                        </button>
                        
                        <?php if ($link_dettagli) : ?>
                        <a href="<?php echo esc_url($link_dettagli); ?>" class="action-btn secondary-btn" target="_blank">
                            <span class="btn-icon">🔗</span>
                            <span>Maggiori Dettagli</span>
                        </a>
                        <?php endif; ?>
                        
                        <button class="action-btn secondary-btn" onclick="shareEvent()">
                            <span class="btn-icon">📤</span>
                            <span>Condividi</span>
                        </button>
                    </div>
                    
                    <!-- Share Modal (nascosto inizialmente) -->
                    <div class="share-modal" id="shareModal">
                        <div class="share-modal-content">
                            <div class="share-modal-header">
                                <h3>Condividi questa celebrazione</h3>
                                <button class="close-modal" onclick="closeShareModal()">&times;</button>
                            </div>
                            <div class="share-buttons">
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php the_permalink(); ?>" 
                                   target="_blank" class="share-btn facebook">
                                    <span class="share-icon">📘</span>
                                    <span>Facebook</span>
                                </a>
                                <a href="https://twitter.com/intent/tweet?url=<?php the_permalink(); ?>&text=<?php echo urlencode(get_the_title()); ?>" 
                                   target="_blank" class="share-btn twitter">
                                    <span class="share-icon">𝕏</span>
                                    <span>X (Twitter)</span>
                                </a>
                                <a href="https://wa.me/?text=<?php echo urlencode(get_the_title() . ' - ' . get_permalink()); ?>" 
                                   target="_blank" class="share-btn whatsapp">
                                    <span class="share-icon">💬</span>
                                    <span>WhatsApp</span>
                                </a>
                                <a href="mailto:?subject=<?php echo urlencode(get_the_title()); ?>&body=<?php echo urlencode(get_permalink()); ?>" 
                                   class="share-btn email">
                                    <span class="share-icon">📧</span>
                                    <span>Email</span>
                                </a>
                                <button class="share-btn copy-link" onclick="copyToClipboard()">
                                    <span class="share-icon">🔗</span>
                                    <span>Copia Link</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column - Foto e celebrazioni correlate -->
                <div class="celebrazione-media-column">
                    
                    <!-- Immagine principale -->
                    <div class="celebrazione-featured-image">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('large', array('alt' => get_the_title())); ?>
                        <?php else : ?>
                            <div class="default-image" style="background: <?php echo $config['color']; ?>">
                                <div class="default-icon"><?php echo $config['icon']; ?></div>
                                <div class="default-text"><?php echo $config['label']; ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Prossime celebrazioni -->
                    <div class="related-celebrations-card">
                        <h3 class="card-title">Prossime Celebrazioni</h3>
                        
                        <?php
                        $related_args = array(
                            'post_type' => 'celebrazione',
                            'posts_per_page' => 4,
                            'post__not_in' => array(get_the_ID()),
                            'meta_key' => '_celebrazione_data',
                            'orderby' => 'meta_value',
                            'order' => 'ASC',
                            'post_status' => 'publish',
                            'meta_query' => array(
                                array(
                                    'key' => '_celebrazione_data',
                                    'value' => date('Y-m-d'),
                                    'compare' => '>=',
                                    'type' => 'DATE'
                                )
                            )
                        );
                        
                        $related = new WP_Query($related_args);
                        
                        if ($related->have_posts()) :
                            while ($related->have_posts()) : $related->the_post();
                                $rel_data = get_post_meta(get_the_ID(), '_celebrazione_data', true);
                                $rel_ora = get_post_meta(get_the_ID(), '_celebrazione_ora_inizio', true);
                                $rel_tipo = get_post_meta(get_the_ID(), '_celebrazione_tipo', true);
                                
                                $rel_config = isset($tipo_config[$rel_tipo]) ? $tipo_config[$rel_tipo] : $tipo_config['celebrazioni'];
                                $rel_timestamp = strtotime($rel_data);
                        ?>
                        <div class="related-item">
                            <div class="related-date">
                                <div class="date-num"><?php echo date('d', $rel_timestamp); ?></div>
                                <div class="date-mon"><?php echo strtoupper(date_i18n('M', $rel_timestamp)); ?></div>
                            </div>
                            <div class="related-info">
                                <div class="related-type" style="color: <?php echo $rel_config['color']; ?>">
                                    <?php echo $rel_config['icon']; ?> <?php echo $rel_config['label']; ?>
                                </div>
                                <h4 class="related-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h4>
                                <?php if ($rel_ora) : ?>
                                <div class="related-time">🕐 <?php echo $rel_ora; ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php
                            endwhile;
                            wp_reset_postdata();
                        else :
                        ?>
                        <div class="no-related">
                            <p>Nessuna celebrazione programmata al momento.</p>
                            <a href="<?php echo get_post_type_archive_link('celebrazione'); ?>" class="view-all-link">
                                Vedi tutte le celebrazioni →
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Contatti rapidi - Solo se compilati -->
                    <?php 
                    $has_contacts = $telefono || $email || $sito_web || $indirizzo;
                    if ($has_contacts) : ?>
                    <div class="contact-info-card">
                        <h3 class="card-title">Informazioni & Contatti</h3>
                        <div class="contact-items">
                            <?php if ($telefono) : ?>
                            <div class="contact-item">
                                <span class="contact-icon">📞</span>
                                <div>
                                    <div class="contact-label">Telefono</div>
                                    <div class="contact-value">
                                        <a href="tel:<?php echo esc_attr(str_replace(' ', '', $telefono)); ?>"><?php echo esc_html($telefono); ?></a>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($email) : ?>
                            <div class="contact-item">
                                <span class="contact-icon">📧</span>
                                <div>
                                    <div class="contact-label">Email</div>
                                    <div class="contact-value">
                                        <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($sito_web) : ?>
                            <div class="contact-item">
                                <span class="contact-icon">🌐</span>
                                <div>
                                    <div class="contact-label">Sito web</div>
                                    <div class="contact-value">
                                        <a href="<?php echo esc_url($sito_web); ?>" target="_blank" rel="noopener"><?php echo esc_html(str_replace(['http://', 'https://'], '', $sito_web)); ?></a>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($indirizzo) : ?>
                            <div class="contact-item">
                                <span class="contact-icon">📍</span>
                                <div>
                                    <div class="contact-label">Indirizzo</div>
                                    <div class="contact-value"><?php echo nl2br(esc_html($indirizzo)); ?></div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php endwhile; ?>
    


<script>
// Funzione per aggiungere al calendario
function addToCalendar() {
    const title = "<?php echo esc_js(get_the_title()); ?>";
    const date = "<?php echo esc_js($data); ?>";
    const startTime = "<?php echo esc_js($ora_inizio); ?>";
    const endTime = "<?php echo esc_js($ora_fine); ?>";
    
    if (!date) {
        alert('Data non disponibile');
        return;
    }
    
    // Formato per Google Calendar
    const startDateTime = date.replace(/-/g, '') + (startTime ? 'T' + startTime.replace(':', '') + '00' : 'T090000');
    const endDateTime = date.replace(/-/g, '') + (endTime ? 'T' + endTime.replace(':', '') + '00' : 'T100000');
    
    const googleCalendarUrl = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${encodeURIComponent(title)}&dates=${startDateTime}/${endDateTime}&details=${encodeURIComponent('Celebrazione liturgica - ' + window.location.href)}`;
    
    window.open(googleCalendarUrl, '_blank');
}

// Funzione per aprire il modal di condivisione
function shareEvent() {
    document.getElementById('shareModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

// Funzione per chiudere il modal
function closeShareModal() {
    document.getElementById('shareModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Funzione per copiare il link
function copyToClipboard() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(function() {
        const btn = document.querySelector('.copy-link span:last-child');
        const originalText = btn.textContent;
        btn.textContent = 'Copiato!';
        setTimeout(() => {
            btn.textContent = originalText;
        }, 2000);
    });
}

// Chiudi modal cliccando fuori
document.getElementById('shareModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeShareModal();
    }
});

// Chiudi modal con ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeShareModal();
    }
});
</script>

<?php get_footer(); ?>