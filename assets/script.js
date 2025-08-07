// Script.js - Celebrazioni Liturgiche Plugin - SCORRIMENTO CORRETTO

jQuery(document).ready(function($) {
    
    // Carousel variables
    const $carousel = $('.celebrazioni-carousel');
    const $wrapper = $('.celebrazioni-wrapper');
    let $cards = $('.celebrazione-card');
    let $dots = $('.dot');
    
    if ($wrapper.length === 0 || $cards.length === 0) return;
    
    // Configurazione carousel
    let currentSlide = 0;
    let isScrolling = false;
    let cardWidth = 0;
    
    // Calcola la larghezza delle card
    function updateCardWidth() {
        if ($cards.length > 0) {
            cardWidth = $cards.first().outerWidth(true); // Include margin
        }
    }
    
    // Determina quante card mostrare contemporaneamente (massimo 6) - MOBILE OTTIMIZZATO
    function getCardsToShow() {
        const containerWidth = $carousel.width();
        if (containerWidth < 480) return 1; // Mobile piccolo
        if (containerWidth < 600) return 1; // Mobile
        if (containerWidth < 768) return 1; // Mobile grande
        if (containerWidth < 900) return 2; // Tablet piccolo
        if (containerWidth < 1200) return 3; // Tablet
        if (containerWidth < 1500) return 4; // Desktop piccolo
        if (containerWidth < 1800) return 5; // Desktop medio
        return 6; // Desktop grande - massimo 6 card visibili
    }
    
    // Sposta il carousel alla slide specifica
    function goToSlide(slideIndex) {
        updateCardWidth();
        
        const totalCards = $cards.length;
        const cardsVisible = getCardsToShow();
        
        // Calcola quante "pagine" di scorrimento ci sono
        // Se abbiamo 10 card e ne mostriamo 6, possiamo scorrere di 4 posizioni (10-6)
        const maxSlide = Math.max(0, totalCards - cardsVisible);
        
        // Limita l'indice della slide
        currentSlide = Math.max(0, Math.min(slideIndex, maxSlide));
        
        // Calcola la posizione di traslazione (una card alla volta)
        const translateX = -(currentSlide * cardWidth);
        
        console.log('goToSlide:', {
            slideIndex: slideIndex,
            currentSlide: currentSlide,
            totalCards: totalCards,
            cardsVisible: cardsVisible,
            maxSlide: maxSlide,
            cardWidth: cardWidth,
            translateX: translateX
        });
        
        $wrapper.css({
            'transform': 'translateX(' + translateX + 'px)',
            'transition': 'transform 0.5s ease'
        });
        
        updateActiveDot();
    }
    
    // Aggiorna il pallino attivo
    function updateActiveDot() {
        $dots.removeClass('active');
        if ($dots.length > currentSlide) {
            $dots.eq(currentSlide).addClass('active');
        }
    }
    
    // Rigenera i pallini
    function regenerateDots() {
        const totalCards = $cards.length;
        const cardsVisible = getCardsToShow();
        
        // Calcola il numero di "pagine" o posizioni di scorrimento
        const maxSlides = Math.max(1, totalCards - cardsVisible + 1);
        
        let dotsHtml = '';
        for (let i = 0; i < maxSlides; i++) {
            dotsHtml += '<span class="dot' + (i === currentSlide ? ' active' : '') + '" data-slide="' + i + '"></span>';
        }
        $('.celebrazioni-dots').html(dotsHtml);
        
        // Mostra/nascondi i dots se necessario
        if (maxSlides <= 1) {
            $('.celebrazioni-dots').hide();
        } else {
            $('.celebrazioni-dots').show();
        }
        
        // Aggiorna i riferimenti
        $dots = $('.dot');
        
        console.log('Dots regenerated:', {
            totalCards: totalCards,
            cardsVisible: cardsVisible,
            maxSlides: maxSlides,
            dotsCount: $dots.length
        });
    }
    
    // Navigation con pallini - delegated event
    $(document).on('click', '.dot', function(e) {
        e.preventDefault();
        const targetSlide = parseInt($(this).data('slide'));
        console.log('Dot clicked:', targetSlide);
        if (!isNaN(targetSlide)) {
            goToSlide(targetSlide);
        }
    });
    
    // Mouse drag functionality
    let isDragging = false;
    let dragStartX = 0;
    let dragStartTranslateX = 0;
    
    // Ottieni la posizione X corrente del transform
    function getCurrentTranslateX() {
        const transform = $wrapper.css('transform');
        if (transform && transform !== 'none') {
            const matrix = transform.replace(/[^0-9\-.,]/g, '').split(',');
            return parseFloat(matrix[4]) || 0;
        }
        return 0;
    }
    
    // Mouse events
    $carousel.on('mousedown', function(e) {
        // Ignora se si clicca su bottoni, link o dots
        if ($(e.target).closest('.celebrazione-btn, a, .dot').length > 0) return;
        
        isDragging = true;
        dragStartX = e.pageX;
        dragStartTranslateX = getCurrentTranslateX();
        
        $wrapper.css('transition', 'none');
        $carousel.css('cursor', 'grabbing');
        $('body').css('user-select', 'none');
        
        e.preventDefault();
    });
    
    $(document).on('mousemove', function(e) {
        if (!isDragging) return;
        
        e.preventDefault();
        
        const deltaX = e.pageX - dragStartX;
        const newTranslateX = dragStartTranslateX + deltaX;
        
        $wrapper.css('transform', 'translateX(' + newTranslateX + 'px)');
    });
    
    $(document).on('mouseup', function() {
        if (!isDragging) return;
        
        isDragging = false;
        $carousel.css('cursor', 'grab');
        $('body').css('user-select', '');
        
        // Calcola la slide più vicina basandosi sul movimento
        const currentTranslateX = getCurrentTranslateX();
        const deltaX = currentTranslateX - dragStartTranslateX;
        
        let newSlide = currentSlide;
        
        if (Math.abs(deltaX) > cardWidth / 4) { // Soglia per cambiare slide
            if (deltaX > 0) {
                newSlide = currentSlide - 1; // Drag verso destra = slide precedente
            } else {
                newSlide = currentSlide + 1; // Drag verso sinistra = slide successiva
            }
        }
        
        goToSlide(newSlide);
    });
    
    // Touch events per mobile
    let touchStartX = 0;
    let touchStartTranslateX = 0;
    
    $carousel.on('touchstart', function(e) {
        if ($(e.target).closest('.celebrazione-btn, a, .dot').length > 0) return;
        
        touchStartX = e.touches[0].pageX;
        touchStartTranslateX = getCurrentTranslateX();
        $wrapper.css('transition', 'none');
        
        e.preventDefault();
    });
    
    $carousel.on('touchmove', function(e) {
        const deltaX = e.touches[0].pageX - touchStartX;
        const newTranslateX = touchStartTranslateX + deltaX;
        
        $wrapper.css('transform', 'translateX(' + newTranslateX + 'px)');
        
        e.preventDefault();
    });
    
    $carousel.on('touchend', function() {
        const currentTranslateX = getCurrentTranslateX();
        const deltaX = currentTranslateX - touchStartTranslateX;
        
        let newSlide = currentSlide;
        
        if (Math.abs(deltaX) > cardWidth / 4) {
            if (deltaX > 0) {
                newSlide = currentSlide - 1;
            } else {
                newSlide = currentSlide + 1;
            }
        }
        
        goToSlide(newSlide);
    });
    
    // Wheel events - SOLO ORIZZONTALE con throttling migliorato
    let wheelTimeout;
    $carousel.on('wheel', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Previeni scroll multipli rapidi
        if (isScrolling) return;
        
        clearTimeout(wheelTimeout);
        
        let delta = 0;
        
        // Normalizza il delta per diversi browser
        if (e.originalEvent.deltaY !== undefined) {
            delta = e.originalEvent.deltaY;
        } else if (e.originalEvent.wheelDelta !== undefined) {
            delta = -e.originalEvent.wheelDelta;
        } else if (e.originalEvent.detail !== undefined) {
            delta = e.originalEvent.detail * 40;
        }
        
        // Determina la direzione con soglia
        if (Math.abs(delta) > 5) {
            isScrolling = true;
            
            console.log('Wheel event:', delta, 'current slide:', currentSlide);
            
            if (delta > 0) {
                // Scroll verso il basso = vai a destra (slide successiva)
                goToSlide(currentSlide + 1);
            } else {
                // Scroll verso l'alto = vai a sinistra (slide precedente)
                goToSlide(currentSlide - 1);
            }
            
            // Reset del flag dopo animazione
            setTimeout(() => {
                isScrolling = false;
            }, 600);
        }
        
        return false;
    });
    
    // Keyboard navigation
    $(document).on('keydown', function(e) {
        if (!$carousel.is(':hover') && !$carousel.find(':focus').length) return;
        
        if (e.key === 'ArrowLeft') {
            e.preventDefault();
            goToSlide(currentSlide - 1);
        } else if (e.key === 'ArrowRight') {
            e.preventDefault();
            goToSlide(currentSlide + 1);
        }
    });
    
    // Resize handler
    let resizeTimeout;
    $(window).on('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            updateCardWidth();
            regenerateDots();
            goToSlide(currentSlide); // Ricalcola la posizione
        }, 250);
    });
    
    // Layout toggle functionality
    $('.toggle-layout').on('click', function() {
        const currentLayout = $(this).data('layout');
        
        if (currentLayout === 'vertical') {
            $('.celebrazione-card').removeClass('layout-horizontal').addClass('layout-vertical');
            $(this).data('layout', 'horizontal').text('Layout Orizzontale');
        } else {
            $('.celebrazione-card').removeClass('layout-vertical').addClass('layout-horizontal');
            $(this).data('layout', 'vertical').text('Layout Verticale');
        }
        
        // Reinizializza dopo il cambio layout
        setTimeout(() => {
            updateCardWidth();
            goToSlide(currentSlide);
        }, 100);
    });
    
    // Filter celebrations by type
    $('.filter-tipo').on('change', function() {
        const selectedType = $(this).val();
        
        if (selectedType === 'all') {
            $('.celebrazione-card').show();
        } else {
            $('.celebrazione-card').hide();
            $('.celebrazione-card[data-tipo="' + selectedType + '"]').show();
        }
        
        // Aggiorna i riferimenti dopo il filtro
        $cards = $('.celebrazione-card:visible');
        
        currentSlide = 0;
        updateCardWidth();
        regenerateDots();
        goToSlide(0);
    });
    
    // Admin area enhancements
    if ($('body').hasClass('post-type-celebrazione')) {
        $('#celebrazione_data').attr('placeholder', 'gg/mm/aaaa');
        
        $('#celebrazione_ora_inizio, #celebrazione_ora_fine').on('change', function() {
            const startTime = $('#celebrazione_ora_inizio').val();
            const endTime = $('#celebrazione_ora_fine').val();
            
            if (startTime && endTime) {
                if (startTime >= endTime) {
                    alert('L\'ora di fine deve essere successiva all\'ora di inizio');
                    $('#celebrazione_ora_fine').val('');
                }
            }
        });
    }
    
    // AJAX load more celebrations
    let loadingMore = false;
    let page = 1;
    
    $('.link-tutte').on('click', function(e) {
        e.preventDefault();
        
        if (loadingMore) return;
        
        loadingMore = true;
        const $link = $(this);
        const originalText = $link.text();
        
        $link.text('Caricamento...');
        
        $.ajax({
            url: celebrazioni_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'load_more_celebrations',
                page: ++page,
                nonce: celebrazioni_ajax.nonce
            },
            success: function(response) {
                if (response.success && response.data.html) {
                    $wrapper.append(response.data.html);
                    
                    // Aggiorna i riferimenti alle card
                    $cards = $('.celebrazione-card');
                    
                    // Ricalcola tutto
                    updateCardWidth();
                    regenerateDots();
                    
                    if (!response.data.has_more) {
                        $link.text('Tutte le celebrazioni caricate').prop('disabled', true);
                    } else {
                        $link.text(originalText);
                    }
                } else {
                    $link.text('Nessuna altra celebrazione');
                }
                
                loadingMore = false;
            },
            error: function() {
                $link.text(originalText);
                loadingMore = false;
            }
        });
    });
    
    // Previeni il comportamento di default sui link durante il drag
    $carousel.on('click', 'a', function(e) {
        if (isDragging) {
            e.preventDefault();
        }
    });
    
    // Inizializzazione
    function initCarousel() {
        console.log('Initializing carousel with', $cards.length, 'cards');
        updateCardWidth();
        regenerateDots();
        $carousel.css('cursor', 'grab');
        currentSlide = 0;
        
        // Posiziona il carousel all'inizio
        $wrapper.css('transform', 'translateX(0px)');
        updateActiveDot();
    }
    
    // Avvia l'inizializzazione
    initCarousel();
    
    // Debug: mostra informazioni essenziali
    console.log('Carousel initialized with', $cards.length, 'cards');
});