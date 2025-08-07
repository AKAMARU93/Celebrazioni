// Archivio Celebrazioni - Script Moderno

document.addEventListener('DOMContentLoaded', function() {
    const oggi = new Date();
    let dataCorrente = new Date(oggi.getFullYear(), oggi.getMonth(), 1);
    let giornoSelezionato = oggi;
    let eventiCaricati = {};

    // Nomi dei mesi e giorni in italiano
    const nomiMesi = [
        'Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno',
        'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'
    ];

    const nomiGiorni = ['Dom', 'Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab'];

    // Dati di esempio degli eventi (sostituire con chiamate AJAX al database WordPress)
    const eventiData = {
        '2025-08-07': [
            {
                titolo: 'Santa Messa Mattutina',
                orario: '08:00',
                luogo: 'Chiesa Principale',
                descrizione: 'Celebrazione eucaristica del mattino per iniziare la giornata in preghiera e comunione con il Signore.'
            },
            {
                titolo: 'Adorazione Eucaristica',
                orario: '17:00 - 18:00',
                luogo: 'Cappella del Santissimo',
                descrizione: 'Momento di adorazione silenziosa davanti al Santissimo Sacramento esposto.'
            }
        ],
        '2025-08-15': [
            {
                titolo: 'Assunzione di Maria Santissima',
                orario: '10:00',
                luogo: 'Chiesa Principale',
                descrizione: 'Solenne celebrazione dell\'Assunzione della Beata Vergine Maria in Cielo con canto del Te Deum.'
            },
            {
                titolo: 'Processione Mariana',
                orario: '18:00',
                luogo: 'Partenza dalla Chiesa',
                descrizione: 'Processione per le vie del centro storico in onore della Madonna Assunta in Cielo.'
            },
            {
                titolo: 'Benedizione Eucaristica',
                orario: '20:00',
                luogo: 'Sagrato della Chiesa',
                descrizione: 'Conclusione della giornata festiva con la solenne benedizione eucaristica.'
            }
        ],
        '2025-08-25': [
            {
                titolo: 'San Bartolomeo Apostolo',
                orario: '09:30',
                luogo: 'Chiesa Principale',
                descrizione: 'Festa patronale di San Bartolomeo Apostolo con messa solenne e benedizione speciale.'
            }
        ],
        '2025-09-08': [
            {
                titolo: 'Natività della Beata Vergine Maria',
                orario: '10:00',
                luogo: 'Santuario Mariano',
                descrizione: 'Celebrazione della nascita della Madonna con particolare devozione e canti mariani.'
            }
        ],
        '2025-09-29': [
            {
                titolo: 'Santi Arcangeli Michele, Gabriele e Raffaele',
                orario: '18:30',
                luogo: 'Chiesa Principale',
                descrizione: 'Festa degli Arcangeli con preghiera speciale di protezione per la comunità.'
            }
        ],
        '2025-10-04': [
            {
                titolo: 'San Francesco d\'Assisi',
                orario: '10:00',
                luogo: 'Chiesa di San Francesco',
                descrizione: 'Solennità del Serafico Padre San Francesco con benedizione degli animali.'
            }
        ],
        '2025-10-07': [
            {
                titolo: 'Beata Vergine del Rosario',
                orario: '17:30',
                luogo: 'Chiesa del Rosario',
                descrizione: 'Recita solenne del Santo Rosario e processione aux flambeaux.'
            }
        ],
        '2025-11-01': [
            {
                titolo: 'Solennità di Tutti i Santi',
                orario: '10:00',
                luogo: 'Chiesa Principale',
                descrizione: 'Solenne celebrazione di Tutti i Santi con particolare ricordo dei beati e santi della nostra diocesi.'
            }
        ],
        '2025-11-02': [
            {
                titolo: 'Commemorazione dei Defunti',
                orario: '10:00',
                luogo: 'Cimitero Comunale',
                descrizione: 'Santa Messa presso il cimitero per tutti i nostri cari defunti con benedizione delle tombe.'
            },
            {
                titolo: 'Suffragio Solenne',
                orario: '18:00',
                luogo: 'Chiesa Principale',
                descrizione: 'Celebrazione eucaristica di suffragio per tutte le anime del purgatorio.'
            }
        ],
        '2025-12-08': [
            {
                titolo: 'Immacolata Concezione',
                orario: '10:00',
                luogo: 'Chiesa Principale',
                descrizione: 'Solennità dell\'Immacolata Concezione della Beata Vergine Maria, Patrona d\'Italia.'
            }
        ],
        '2025-12-24': [
            {
                titolo: 'Messa della Vigilia di Natale',
                orario: '18:00',
                luogo: 'Chiesa Principale',
                descrizione: 'Celebrazione della Vigilia di Natale con il canto solenne del Gloria in excelsis Deo.'
            },
            {
                titolo: 'Messa della Notte di Natale',
                orario: '23:30',
                luogo: 'Chiesa Principale',
                descrizione: 'Solenne Messa della Notte Santa con adorazione del Bambino Gesù nel presepe.'
            }
        ],
        '2025-12-25': [
            {
                titolo: 'Natale - Messa dell\'Aurora',
                orario: '06:00',
                luogo: 'Grotta del Presepe',
                descrizione: 'Messa dell\'Aurora nella suggestiva atmosfera della grotta del presepe.'
            },
            {
                titolo: 'Natale - Messa del Giorno',
                orario: '10:00',
                luogo: 'Chiesa Principale',
                descrizione: 'Messa solenne del Santo Natale con canto del Te Deum di ringraziamento.'
            }
        ]
    };

    // Inizializzazione
    inizializza();

    function inizializza() {
        generaCalendario();
        caricaEventiGiorno(giornoSelezionato);
        
        // Event listeners
        document.getElementById('prevMonth').addEventListener('click', mesePrec);
        document.getElementById('nextMonth').addEventListener('click', meseSucc);
        
        // Keyboard navigation
        document.addEventListener('keydown', handleKeyboard);
    }

    function generaCalendario() {
        const calendario = document.getElementById('calendario');
        const currentMonthEl = document.getElementById('currentMonth');
        
        if (!calendario || !currentMonthEl) return;
        
        // Aggiorna il titolo del mese
        currentMonthEl.textContent = `${nomiMesi[dataCorrente.getMonth()]} ${dataCorrente.getFullYear()}`;
        
        // Pulisci il calendario
        calendario.innerHTML = '';
        
        // Aggiungi i nomi dei giorni della settimana
        nomiGiorni.forEach(giorno => {
            const giornoEl = document.createElement('div');
            giornoEl.className = 'giorno-settimana';
            giornoEl.textContent = giorno;
            calendario.appendChild(giornoEl);
        });
        
        // Calcola date del mese
        const primoGiorno = new Date(dataCorrente.getFullYear(), dataCorrente.getMonth(), 1);
        const ultimoGiorno = new Date(dataCorrente.getFullYear(), dataCorrente.getMonth() + 1, 0);
        const primoGiornoSettimana = primoGiorno.getDay();
        
        // Giorni del mese precedente
        const mesePrec = new Date(dataCorrente.getFullYear(), dataCorrente.getMonth() - 1, 0);
        for (let i = primoGiornoSettimana - 1; i >= 0; i--) {
            const giorno = mesePrec.getDate() - i;
            const giornoEl = creaElementoGiorno(giorno, true, new Date(mesePrec.getFullYear(), mesePrec.getMonth(), giorno));
            calendario.appendChild(giornoEl);
        }
        
        // Giorni del mese corrente
        for (let giorno = 1; giorno <= ultimoGiorno.getDate(); giorno++) {
            const dataGiorno = new Date(dataCorrente.getFullYear(), dataCorrente.getMonth(), giorno);
            const giornoEl = creaElementoGiorno(giorno, false, dataGiorno);
            calendario.appendChild(giornoEl);
        }
        
        // Giorni del mese successivo
        const giorniTotali = calendario.children.length - 7;
        const giorniMancanti = 42 - giorniTotali;
        for (let giorno = 1; giorno <= giorniMancanti; giorno++) {
            const meseSucc = new Date(dataCorrente.getFullYear(), dataCorrente.getMonth() + 1, giorno);
            const giornoEl = creaElementoGiorno(giorno, true, meseSucc);
            calendario.appendChild(giornoEl);
        }
    }

    function creaElementoGiorno(giorno, altroMese, dataGiorno) {
        const giornoEl = document.createElement('div');
        giornoEl.className = 'giorno';
        giornoEl.textContent = giorno;
        giornoEl.tabIndex = altroMese ? -1 : 0;
        
        const dataString = formatData(dataGiorno);
        
        if (altroMese) {
            giornoEl.classList.add('altro-mese');
        }
        
        // Controlla se è oggi
        if (dataString === formatData(oggi)) {
            giornoEl.classList.add('oggi');
        }
        
        // Controlla se è selezionato
        if (dataString === formatData(giornoSelezionato)) {
            giornoEl.classList.add('selezionato');
        }
        
        // Controlla se ci sono eventi
        if (eventiData[dataString]) {
            giornoEl.classList.add('con-eventi');
        }
        
        // Event listener per il click
        if (!altroMese) {
            giornoEl.addEventListener('click', function() {
                selezionaGiorno(dataGiorno, giornoEl);
            });
        }
        
        return giornoEl;
    }

    function selezionaGiorno(data, elemento) {
        // Rimuovi selezione precedente
        document.querySelectorAll('.giorno.selezionato').forEach(el => {
            el.classList.remove('selezionato');
        });
        
        // Seleziona nuovo giorno
        elemento.classList.add('selezionato');
        giornoSelezionato = data;
        
        // Carica eventi
        caricaEventiGiorno(data);
    }

    function caricaEventiGiorno(data) {
        const dataString = formatData(data);
        const eventiLista = document.getElementById('eventiLista');
        const selectedDate = document.getElementById('selectedDate');
        const eventsCount = document.getElementById('eventsCount');
        
        if (!eventiLista || !selectedDate || !eventsCount) return;
        
        // Mostra loading
        eventiLista.innerHTML = `
            <div class="eventi-loading">
                <div class="loading-spinner"></div>
                <p>Caricamento eventi...</p>
            </div>
        `;
        
        // Simula caricamento
        setTimeout(() => {
            // Aggiorna titolo con data formattata
            const opzioni = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            };
            const dataFormattata = data.toLocaleDateString('it-IT', opzioni);
            const dataCapitalizzata = dataFormattata.charAt(0).toUpperCase() + dataFormattata.slice(1);
            selectedDate.textContent = `Eventi del ${dataCapitalizzata}`;
            
            // Carica eventi
            const eventi = eventiData[dataString] || [];
            eventsCount.textContent = eventi.length;
            
            if (eventi.length > 0) {
                eventiLista.innerHTML = '';
                eventi.forEach((evento, index) => {
                    const eventoEl = document.createElement('div');
                    eventoEl.className = 'evento-card';
                    eventoEl.style.animationDelay = `${index * 0.1}s`;
                    
                    eventoEl.innerHTML = `
                        <div class="evento-titolo">${evento.titolo}</div>
                        <div class="evento-meta">
                            ${evento.orario ? `<div class="evento-orario">${evento.orario}</div>` : ''}
                            ${evento.luogo ? `<div class="evento-luogo">${evento.luogo}</div>` : ''}
                        </div>
                        <div class="evento-descrizione">${evento.descrizione}</div>
                    `;
                    
                    eventiLista.appendChild(eventoEl);
                });
            } else {
                eventiLista.innerHTML = `
                    <div class="eventi-vuoti">
                        <h3>Nessun evento programmato</h3>
                        <p>Non ci sono celebrazioni liturgiche programmate per questo giorno.</p>
                    </div>
                `;
            }
        }, 500);
    }

    function mesePrec() {
        dataCorrente = new Date(dataCorrente.getFullYear(), dataCorrente.getMonth() - 1, 1);
        generaCalendario();
    }

    function meseSucc() {
        dataCorrente = new Date(dataCorrente.getFullYear(), dataCorrente.getMonth() + 1, 1);
        generaCalendario();
    }

    function handleKeyboard(e) {
        if (e.target.classList.contains('giorno') && !e.target.classList.contains('altro-mese')) {
            const giorni = Array.from(document.querySelectorAll('.giorno:not(.altro-mese)'));
            const currentIndex = giorni.indexOf(e.target);
            let newIndex;
            
            switch(e.key) {
                case 'ArrowLeft':
                    e.preventDefault();
                    newIndex = Math.max(0, currentIndex - 1);
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    newIndex = Math.min(giorni.length - 1, currentIndex + 1);
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    newIndex = Math.max(0, currentIndex - 7);
                    break;
                case 'ArrowDown':
                    e.preventDefault();
                    newIndex = Math.min(giorni.length - 1, currentIndex + 7);
                    break;
                case 'Enter':
                case ' ':
                    e.preventDefault();
                    e.target.click();
                    return;
                default:
                    return;
            }
            
            if (newIndex !== undefined && giorni[newIndex]) {
                giorni[newIndex].focus();
            }
        }
    }

    function formatData(data) {
        const anno = data.getFullYear();
        const mese = String(data.getMonth() + 1).padStart(2, '0');
        const giorno = String(data.getDate()).padStart(2, '0');
        return `${anno}-${mese}-${giorno}`;
    }

    // Funzioni per integrazione con WordPress (da implementare se necessario)
    function caricaEventiDaDatabase(data) {
        // Implementa chiamata AJAX a WordPress
        // fetch('/wp-admin/admin-ajax.php', {
        //     method: 'POST',
        //     headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        //     body: `action=get_celebrazioni_by_date&date=${formatData(data)}&nonce=${archivio_vars.nonce}`
        // })
        // .then(response => response.json())
        // .then(data => {
        //     // Gestisci risposta
        // });
    }

    function caricaDateConEventi() {
        // Implementa chiamata AJAX per ottenere tutte le date con eventi
        // fetch('/wp-admin/admin-ajax.php', {
        //     method: 'POST',
        //     headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        //     body: `action=get_dates_with_events&nonce=${archivio_vars.nonce}`
        // })
        // .then(response => response.json())
        // .then(dates => {
        //     // Aggiorna calendario con date che hanno eventi
        // });
    }

    // Esporta funzioni per uso esterno
    window.ArchivioCelebrazioni = {
        selezionaData: function(data) {
            giornoSelezionato = new Date(data);
            dataCorrente = new Date(giornoSelezionato.getFullYear(), giornoSelezionato.getMonth(), 1);
            generaCalendario();
            caricaEventiGiorno(giornoSelezionato);
        },
        ottieniDataSelezionata: function() {
            return giornoSelezionato;
        },
        aggiornaBanche: function() {
            generaCalendario();
        }
    };
});
                    