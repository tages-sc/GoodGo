<?php

return [

    'accepted' => 'Il campo :attribute deve essere accettato.',
    'accepted_if' => 'Il campo :attribute deve essere accettato quando :other è :value.',
    'active_url' => 'Il campo :attribute deve essere un URL valido.',
    'after' => 'Il campo :attribute deve essere una data successiva a :date.',
    'after_or_equal' => 'Il campo :attribute deve essere una data successiva o uguale a :date.',
    'alpha' => 'Il campo :attribute deve contenere solo lettere.',
    'alpha_dash' => 'Il campo :attribute deve contenere solo lettere, numeri, trattini e underscore.',
    'alpha_num' => 'Il campo :attribute deve contenere solo lettere e numeri.',
    'any_of' => 'Il campo :attribute non è valido.',
    'array' => 'Il campo :attribute deve essere un array.',
    'ascii' => 'Il campo :attribute deve contenere solo caratteri alfanumerici e simboli a byte singolo.',
    'before' => 'Il campo :attribute deve essere una data precedente a :date.',
    'before_or_equal' => 'Il campo :attribute deve essere una data precedente o uguale a :date.',
    'between' => [
        'array' => 'Il campo :attribute deve avere tra :min e :max elementi.',
        'file' => 'Il campo :attribute deve essere tra :min e :max kilobyte.',
        'numeric' => 'Il campo :attribute deve essere tra :min e :max.',
        'string' => 'Il campo :attribute deve essere tra :min e :max caratteri.',
    ],
    'boolean' => 'Il campo :attribute deve essere vero o falso.',
    'can' => 'Il campo :attribute contiene un valore non autorizzato.',
    'confirmed' => 'La conferma del campo :attribute non corrisponde.',
    'contains' => 'Nel campo :attribute manca un valore richiesto.',
    'current_password' => 'La password non è corretta.',
    'date' => 'Il campo :attribute deve essere una data valida.',
    'date_equals' => 'Il campo :attribute deve essere una data uguale a :date.',
    'date_format' => 'Il campo :attribute deve corrispondere al formato :format.',
    'decimal' => 'Il campo :attribute deve avere :decimal cifre decimali.',
    'declined' => 'Il campo :attribute deve essere rifiutato.',
    'declined_if' => 'Il campo :attribute deve essere rifiutato quando :other è :value.',
    'different' => 'I campi :attribute e :other devono essere diversi.',
    'digits' => 'Il campo :attribute deve essere di :digits cifre.',
    'digits_between' => 'Il campo :attribute deve essere tra :min e :max cifre.',
    'dimensions' => 'Il campo :attribute ha dimensioni immagine non valide.',
    'distinct' => 'Il campo :attribute ha un valore duplicato.',
    'doesnt_contain' => 'Il campo :attribute non deve contenere: :values.',
    'doesnt_end_with' => 'Il campo :attribute non deve terminare con: :values.',
    'doesnt_start_with' => 'Il campo :attribute non deve iniziare con: :values.',
    'email' => 'Il campo :attribute deve essere un indirizzo email valido.',
    'encoding' => 'Il campo :attribute deve essere codificato in :encoding.',
    'ends_with' => 'Il campo :attribute deve terminare con uno dei seguenti: :values.',
    'enum' => 'Il valore selezionato per :attribute non è valido.',
    'exists' => 'Il valore selezionato per :attribute non è valido.',
    'extensions' => 'Il campo :attribute deve avere una delle seguenti estensioni: :values.',
    'file' => 'Il campo :attribute deve essere un file.',
    'filled' => 'Il campo :attribute deve avere un valore.',
    'gt' => [
        'array' => 'Il campo :attribute deve avere più di :value elementi.',
        'file' => 'Il campo :attribute deve essere maggiore di :value kilobyte.',
        'numeric' => 'Il campo :attribute deve essere maggiore di :value.',
        'string' => 'Il campo :attribute deve avere più di :value caratteri.',
    ],
    'gte' => [
        'array' => 'Il campo :attribute deve avere :value o più elementi.',
        'file' => 'Il campo :attribute deve essere maggiore o uguale a :value kilobyte.',
        'numeric' => 'Il campo :attribute deve essere maggiore o uguale a :value.',
        'string' => 'Il campo :attribute deve avere :value o più caratteri.',
    ],
    'hex_color' => 'Il campo :attribute deve essere un colore esadecimale valido.',
    'image' => 'Il campo :attribute deve essere un\'immagine.',
    'in' => 'Il valore selezionato per :attribute non è valido.',
    'in_array' => 'Il campo :attribute deve esistere in :other.',
    'in_array_keys' => 'Il campo :attribute deve contenere almeno una delle seguenti chiavi: :values.',
    'integer' => 'Il campo :attribute deve essere un numero intero.',
    'ip' => 'Il campo :attribute deve essere un indirizzo IP valido.',
    'ipv4' => 'Il campo :attribute deve essere un indirizzo IPv4 valido.',
    'ipv6' => 'Il campo :attribute deve essere un indirizzo IPv6 valido.',
    'json' => 'Il campo :attribute deve essere una stringa JSON valida.',
    'list' => 'Il campo :attribute deve essere una lista.',
    'lowercase' => 'Il campo :attribute deve essere in minuscolo.',
    'lt' => [
        'array' => 'Il campo :attribute deve avere meno di :value elementi.',
        'file' => 'Il campo :attribute deve essere inferiore a :value kilobyte.',
        'numeric' => 'Il campo :attribute deve essere inferiore a :value.',
        'string' => 'Il campo :attribute deve avere meno di :value caratteri.',
    ],
    'lte' => [
        'array' => 'Il campo :attribute non deve avere più di :value elementi.',
        'file' => 'Il campo :attribute deve essere inferiore o uguale a :value kilobyte.',
        'numeric' => 'Il campo :attribute deve essere inferiore o uguale a :value.',
        'string' => 'Il campo :attribute deve avere al massimo :value caratteri.',
    ],
    'mac_address' => 'Il campo :attribute deve essere un indirizzo MAC valido.',
    'max' => [
        'array' => 'Il campo :attribute non deve avere più di :max elementi.',
        'file' => 'Il campo :attribute non deve superare :max kilobyte.',
        'numeric' => 'Il campo :attribute non deve essere superiore a :max.',
        'string' => 'Il campo :attribute non deve superare i :max caratteri.',
    ],
    'max_digits' => 'Il campo :attribute non deve avere più di :max cifre.',
    'mimes' => 'Il campo :attribute deve essere un file di tipo: :values.',
    'mimetypes' => 'Il campo :attribute deve essere un file di tipo: :values.',
    'min' => [
        'array' => 'Il campo :attribute deve avere almeno :min elementi.',
        'file' => 'Il campo :attribute deve essere almeno :min kilobyte.',
        'numeric' => 'Il campo :attribute deve essere almeno :min.',
        'string' => 'Il campo :attribute deve avere almeno :min caratteri.',
    ],
    'min_digits' => 'Il campo :attribute deve avere almeno :min cifre.',
    'missing' => 'Il campo :attribute deve essere assente.',
    'missing_if' => 'Il campo :attribute deve essere assente quando :other è :value.',
    'missing_unless' => 'Il campo :attribute deve essere assente a meno che :other non sia :value.',
    'missing_with' => 'Il campo :attribute deve essere assente quando :values è presente.',
    'missing_with_all' => 'Il campo :attribute deve essere assente quando :values sono presenti.',
    'multiple_of' => 'Il campo :attribute deve essere un multiplo di :value.',
    'not_in' => 'Il valore selezionato per :attribute non è valido.',
    'not_regex' => 'Il formato del campo :attribute non è valido.',
    'numeric' => 'Il campo :attribute deve essere un numero.',
    'password' => [
        'letters' => 'Il campo :attribute deve contenere almeno una lettera.',
        'mixed' => 'Il campo :attribute deve contenere almeno una lettera maiuscola e una minuscola.',
        'numbers' => 'Il campo :attribute deve contenere almeno un numero.',
        'symbols' => 'Il campo :attribute deve contenere almeno un simbolo.',
        'uncompromised' => 'La :attribute inserita è apparsa in una fuga di dati. Scegli una :attribute diversa.',
    ],
    'present' => 'Il campo :attribute deve essere presente.',
    'present_if' => 'Il campo :attribute deve essere presente quando :other è :value.',
    'present_unless' => 'Il campo :attribute deve essere presente a meno che :other non sia :value.',
    'present_with' => 'Il campo :attribute deve essere presente quando :values è presente.',
    'present_with_all' => 'Il campo :attribute deve essere presente quando :values sono presenti.',
    'prohibited' => 'Il campo :attribute è vietato.',
    'prohibited_if' => 'Il campo :attribute è vietato quando :other è :value.',
    'prohibited_if_accepted' => 'Il campo :attribute è vietato quando :other è accettato.',
    'prohibited_if_declined' => 'Il campo :attribute è vietato quando :other è rifiutato.',
    'prohibited_unless' => 'Il campo :attribute è vietato a meno che :other non sia in :values.',
    'prohibits' => 'Il campo :attribute impedisce la presenza di :other.',
    'regex' => 'Il formato del campo :attribute non è valido.',
    'required' => 'Il campo :attribute è obbligatorio.',
    'required_array_keys' => 'Il campo :attribute deve contenere le voci: :values.',
    'required_if' => 'Il campo :attribute è obbligatorio quando :other è :value.',
    'required_if_accepted' => 'Il campo :attribute è obbligatorio quando :other è accettato.',
    'required_if_declined' => 'Il campo :attribute è obbligatorio quando :other è rifiutato.',
    'required_unless' => 'Il campo :attribute è obbligatorio a meno che :other non sia in :values.',
    'required_with' => 'Il campo :attribute è obbligatorio quando :values è presente.',
    'required_with_all' => 'Il campo :attribute è obbligatorio quando :values sono presenti.',
    'required_without' => 'Il campo :attribute è obbligatorio quando :values non è presente.',
    'required_without_all' => 'Il campo :attribute è obbligatorio quando nessuno dei :values è presente.',
    'same' => 'I campi :attribute e :other devono corrispondere.',
    'size' => [
        'array' => 'Il campo :attribute deve contenere :size elementi.',
        'file' => 'Il campo :attribute deve essere di :size kilobyte.',
        'numeric' => 'Il campo :attribute deve essere :size.',
        'string' => 'Il campo :attribute deve essere di :size caratteri.',
    ],
    'starts_with' => 'Il campo :attribute deve iniziare con uno dei seguenti: :values.',
    'string' => 'Il campo :attribute deve essere una stringa.',
    'timezone' => 'Il campo :attribute deve essere un fuso orario valido.',
    'unique' => 'Il campo :attribute è già stato utilizzato.',
    'uploaded' => 'Il caricamento del campo :attribute non è riuscito.',
    'uppercase' => 'Il campo :attribute deve essere in maiuscolo.',
    'url' => 'Il campo :attribute deve essere un URL valido.',
    'ulid' => 'Il campo :attribute deve essere un ULID valido.',
    'uuid' => 'Il campo :attribute deve essere un UUID valido.',

    /*
    |--------------------------------------------------------------------------
    | Messaggi di validazione personalizzati
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Attributi personalizzati
    |--------------------------------------------------------------------------
    |
    | Le seguenti righe sono usate per sostituire il segnaposto :attribute
    | con qualcosa di più leggibile come "Indirizzo Email" invece di "email".
    |
    */

    'attributes' => [
        // Utenti
        'name' => 'nome',
        'email' => 'email',
        'password' => 'password',
        'platform' => 'piattaforma',

        // Gare
        'start_date' => 'data inizio',
        'end_date' => 'data fine',
        'registration_start' => 'inizio iscrizioni',
        'registration_end' => 'fine iscrizioni',
        'max_participants' => 'max partecipanti',
        'min_track_distance' => 'distanza minima',
        'max_track_distance' => 'distanza massima',
        'max_daily_tracks' => 'tracce massime giornaliere',
        'allowed_transport_modes' => 'mezzi di trasporto ammessi',
        'credits_per_km' => 'crediti per km',
        'credits_multiplier' => 'moltiplicatore crediti',
        'credits_to_euro' => 'conversione crediti/euro',
        'max_earning_per_person' => 'guadagno massimo per persona',
        'reward_mode' => 'modalità premi',
        'extension_type' => 'tipo estensione',
        'competition_type' => 'tipo gara',
        'scoring_type' => 'tipo punteggio',
        'leaderboard_type' => 'tipo classifica',
        'max_distance_per_mode' => 'distanza massima per modalità',
        'max_daily_distance_per_mode' => 'distanza giornaliera massima per modalità',
        'age_ranges' => 'fasce di età',
        'questionnaire_url' => 'URL questionario',
        'rules_document' => 'documento regolamento',
        'extra_document' => 'documento aggiuntivo',
        'is_public' => 'gara pubblica',
        'moderated_subscription' => 'iscrizioni moderate',

        // Enti
        'tipologia' => 'tipologia',
        'location' => 'località',
        'descrizione' => 'descrizione',
        'logo' => 'logo',
        'banner' => 'banner',
        'iscrizione_moderata' => 'iscrizione moderata',
        'website' => 'sito web',
        'instagram_url' => 'URL Instagram',
        'linkedin_url' => 'URL LinkedIn',
        'twitter_url' => 'URL Twitter',
        'facebook_url' => 'URL Facebook',
        'colore' => 'colore',

        // Badge
        'slug' => 'slug',
        'description' => 'descrizione',
        'category' => 'categoria',
        'stars' => 'stelle',
        'threshold_type' => 'tipo soglia',
        'threshold_value' => 'valore soglia',
        'is_active' => 'attivo',
        'sort_order' => 'ordine',
        'icon' => 'icona',

        // Occupazioni
        'is_active' => 'attivo',

        // Tracce
        'status' => 'stato',
        'transport_mode' => 'modalità di trasporto',

        // Profilo
        'username' => 'nome utente',
        'birth_date' => 'data di nascita',
        'address' => 'indirizzo',
        'phone' => 'telefono',
        'occupation_id' => 'occupazione',

        // Partner
        'legal_address' => 'indirizzo legale',
        'legal_city' => 'città sede legale',
        'legal_rep_name' => 'rappresentante legale',
        'iban' => 'IBAN',
        'business_name' => 'ragione sociale',
        'vat_number' => 'partita IVA',
        'fiscal_code' => 'codice fiscale',

        // Movimenti/Spese
        'credits_amount' => 'importo crediti',
        'euro_amount' => 'importo euro',

        // Generici
        'image' => 'immagine',
        'file' => 'file',
        'title' => 'titolo',
        'body' => 'contenuto',
        'rules' => 'regolamento',
        'prizes' => 'premi',
    ],

];
