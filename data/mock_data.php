<?php
/**
 * MOCK DATA - Date de test pentru dezvoltare
 * 
 * Acest fișier conține array-uri PHP ce simulează tabelele din Azure SQL.
 * Denumirile câmpurilor sunt IDENTICE cu cele din baza de date reală.
 * 
 * IMPORTANT: Acest fișier va fi înlocuit cu queries reale la baza de date.
 * Repository-urile sunt deja pregătite să comute între mock și DB.
 */

// ============================================
// UTILIZATORI (login credentials)
// ============================================
// Parole hash-uite cu password_hash() - originalul e "parola123" pentru toți
// pentru testare. La producție utilizatorii își setează parole proprii.
$_DEFAULT_HASH = password_hash('parola123', PASSWORD_BCRYPT);

$GLOBALS['MOCK_UTILIZATORI'] = [
    // Medici
    1 => ['id_utilizator' => 1, 'email' => 'popescu@vitalcares.ro', 'parola' => $_DEFAULT_HASH, 'rol' => 'medic'],
    2 => ['id_utilizator' => 2, 'email' => 'ionescu@vitalcares.ro', 'parola' => $_DEFAULT_HASH, 'rol' => 'medic'],
    3 => ['id_utilizator' => 3, 'email' => 'georgescu@vitalcares.ro', 'parola' => $_DEFAULT_HASH, 'rol' => 'medic'],
    
    // Pacienți (15)
    10 => ['id_utilizator' => 10, 'email' => 'ion.popescu@email.ro', 'parola' => $_DEFAULT_HASH, 'rol' => 'pacient'],
    11 => ['id_utilizator' => 11, 'email' => 'maria.ionescu@email.ro', 'parola' => $_DEFAULT_HASH, 'rol' => 'pacient'],
    12 => ['id_utilizator' => 12, 'email' => 'vasile.dumitrescu@email.ro', 'parola' => $_DEFAULT_HASH, 'rol' => 'pacient'],
    13 => ['id_utilizator' => 13, 'email' => 'elena.stancu@email.ro', 'parola' => $_DEFAULT_HASH, 'rol' => 'pacient'],
    14 => ['id_utilizator' => 14, 'email' => 'gheorghe.popa@email.ro', 'parola' => $_DEFAULT_HASH, 'rol' => 'pacient'],
    15 => ['id_utilizator' => 15, 'email' => 'ana.radulescu@email.ro', 'parola' => $_DEFAULT_HASH, 'rol' => 'pacient'],
    16 => ['id_utilizator' => 16, 'email' => 'constantin.matei@email.ro', 'parola' => $_DEFAULT_HASH, 'rol' => 'pacient'],
    17 => ['id_utilizator' => 17, 'email' => 'ileana.dinu@email.ro', 'parola' => $_DEFAULT_HASH, 'rol' => 'pacient'],
    18 => ['id_utilizator' => 18, 'email' => 'nicolae.serban@email.ro', 'parola' => $_DEFAULT_HASH, 'rol' => 'pacient'],
    19 => ['id_utilizator' => 19, 'email' => 'paula.toma@email.ro', 'parola' => $_DEFAULT_HASH, 'rol' => 'pacient'],
    20 => ['id_utilizator' => 20, 'email' => 'mihai.cojocaru@email.ro', 'parola' => $_DEFAULT_HASH, 'rol' => 'pacient'],
    21 => ['id_utilizator' => 21, 'email' => 'rodica.barbu@email.ro', 'parola' => $_DEFAULT_HASH, 'rol' => 'pacient'],
    22 => ['id_utilizator' => 22, 'email' => 'dumitru.olaru@email.ro', 'parola' => $_DEFAULT_HASH, 'rol' => 'pacient'],
    23 => ['id_utilizator' => 23, 'email' => 'florica.neagu@email.ro', 'parola' => $_DEFAULT_HASH, 'rol' => 'pacient'],
    24 => ['id_utilizator' => 24, 'email' => 'stefan.lupu@email.ro', 'parola' => $_DEFAULT_HASH, 'rol' => 'pacient'],
];

// ============================================
// MEDICI
// ============================================
$GLOBALS['MOCK_MEDICI'] = [
    1 => [
        'id' => 1,
        'id_utilizator' => 1,
        'nume' => 'Popescu',
        'prenume' => 'Andrei',
        'specializare' => 'Cardiologie',
        'telefon' => '0721234567',
        'email' => 'popescu@vitalcares.ro',
    ],
    2 => [
        'id' => 2,
        'id_utilizator' => 2,
        'nume' => 'Ionescu',
        'prenume' => 'Mihaela',
        'specializare' => 'Geriatrie',
        'telefon' => '0732345678',
        'email' => 'ionescu@vitalcares.ro',
    ],
    3 => [
        'id' => 3,
        'id_utilizator' => 3,
        'nume' => 'Georgescu',
        'prenume' => 'Radu',
        'specializare' => 'Cardiologie',
        'telefon' => '0743456789',
        'email' => 'georgescu@vitalcares.ro',
    ],
];

// ============================================
// PACIENT (singular, ca în CREATE de la Darius)
// ============================================
$GLOBALS['MOCK_PACIENT'] = [
    1 => [
        'id' => 1,
        'id_utilizator' => 10,
        'id_medic' => 1,
        'nume' => 'Popescu',
        'prenume' => 'Ion',
        'cnp' => '1450203350012',
        'varsta' => 79,
        'strada' => 'Str. Trandafirilor nr. 12',
        'oras' => 'Timișoara',
        'judet' => 'Timiș',
        'telefon' => '0723456789',
        'email' => 'ion.popescu@email.ro',
        'profesie' => 'Pensionar',
        'loc_de_munca' => '-',
        'istoric_medical' => 'HTA stadiul II diagnosticat 2018. Diabet zaharat tip 2 din 2020. Infarct miocardic în 2021 - stent.',
        'alergii' => 'Penicilină, Aspirină',
    ],
    2 => [
        'id' => 2,
        'id_utilizator' => 11,
        'id_medic' => 1,
        'nume' => 'Ionescu',
        'prenume' => 'Maria',
        'cnp' => '2520315350023',
        'varsta' => 72,
        'strada' => 'Bd. Revoluției nr. 45, ap. 3',
        'oras' => 'Timișoara',
        'judet' => 'Timiș',
        'telefon' => '0734567890',
        'email' => 'maria.ionescu@email.ro',
        'profesie' => 'Pensionar (fost contabil)',
        'loc_de_munca' => '-',
        'istoric_medical' => 'Aritmie cardiacă diagnosticată 2019. Osteoporoză. Hipotiroidism.',
        'alergii' => 'Niciuna cunoscută',
    ],
    3 => [
        'id' => 3,
        'id_utilizator' => 12,
        'id_medic' => 1,
        'nume' => 'Dumitrescu',
        'prenume' => 'Vasile',
        'cnp' => '1430812350034',
        'varsta' => 81,
        'strada' => 'Str. Victoriei nr. 78',
        'oras' => 'Timișoara',
        'judet' => 'Timiș',
        'telefon' => '0745678901',
        'email' => 'vasile.dumitrescu@email.ro',
        'profesie' => 'Pensionar (fost inginer)',
        'loc_de_munca' => '-',
        'istoric_medical' => 'Insuficiență cardiacă cronică. HTA. Bronșită cronică.',
        'alergii' => 'Sulfamidă',
    ],
    4 => [
        'id' => 4,
        'id_utilizator' => 13,
        'id_medic' => 2,
        'nume' => 'Stancu',
        'prenume' => 'Elena',
        'cnp' => '2480525350045',
        'varsta' => 76,
        'strada' => 'Calea Aradului nr. 102',
        'oras' => 'Timișoara',
        'judet' => 'Timiș',
        'telefon' => '0756789012',
        'email' => 'elena.stancu@email.ro',
        'profesie' => 'Pensionar (fost profesor)',
        'loc_de_munca' => '-',
        'istoric_medical' => 'Diabet zaharat tip 2. Cataractă operată 2022. Artroză genunchi.',
        'alergii' => 'Iod',
    ],
    5 => [
        'id' => 5,
        'id_utilizator' => 14,
        'id_medic' => 2,
        'nume' => 'Popa',
        'prenume' => 'Gheorghe',
        'cnp' => '1410703350056',
        'varsta' => 83,
        'strada' => 'Str. Bujorilor nr. 21',
        'oras' => 'Timișoara',
        'judet' => 'Timiș',
        'telefon' => '0767890123',
        'email' => 'gheorghe.popa@email.ro',
        'profesie' => 'Pensionar',
        'loc_de_munca' => '-',
        'istoric_medical' => 'Angină pectorală stabilă. HTA. Prostatita cronică.',
        'alergii' => 'Niciuna',
    ],
    6 => [
        'id' => 6,
        'id_utilizator' => 15,
        'id_medic' => 1,
        'nume' => 'Radulescu',
        'prenume' => 'Ana',
        'cnp' => '2550418350067',
        'varsta' => 69,
        'strada' => 'Str. Lalelelor nr. 9',
        'oras' => 'Timișoara',
        'judet' => 'Timiș',
        'telefon' => '0778901234',
        'email' => 'ana.radulescu@email.ro',
        'profesie' => 'Pensionar (fost asistent medical)',
        'loc_de_munca' => '-',
        'istoric_medical' => 'Hipertensiune arterială. Anemie feriprivă.',
        'alergii' => 'Aspirină',
    ],
    7 => [
        'id' => 7,
        'id_utilizator' => 16,
        'id_medic' => 3,
        'nume' => 'Matei',
        'prenume' => 'Constantin',
        'cnp' => '1390612350078',
        'varsta' => 85,
        'strada' => 'Str. Independentei nr. 33',
        'oras' => 'Timișoara',
        'judet' => 'Timiș',
        'telefon' => '0789012345',
        'email' => 'constantin.matei@email.ro',
        'profesie' => 'Pensionar (fost militar)',
        'loc_de_munca' => '-',
        'istoric_medical' => 'Fibrilație atrială. HTA. Demență ușoară.',
        'alergii' => 'Niciuna',
    ],
    8 => [
        'id' => 8,
        'id_utilizator' => 17,
        'id_medic' => 2,
        'nume' => 'Dinu',
        'prenume' => 'Ileana',
        'cnp' => '2530920350089',
        'varsta' => 73,
        'strada' => 'Bd. Liviu Rebreanu nr. 56',
        'oras' => 'Timișoara',
        'judet' => 'Timiș',
        'telefon' => '0790123456',
        'email' => 'ileana.dinu@email.ro',
        'profesie' => 'Pensionar',
        'loc_de_munca' => '-',
        'istoric_medical' => 'Diabet zaharat tip 2 insulino-dependent. Retinopatie diabetică.',
        'alergii' => 'Penicilină',
    ],
    9 => [
        'id' => 9,
        'id_utilizator' => 18,
        'id_medic' => 3,
        'nume' => 'Șerban',
        'prenume' => 'Nicolae',
        'cnp' => '1440114350090',
        'varsta' => 80,
        'strada' => 'Str. Spitalului nr. 17',
        'oras' => 'Timișoara',
        'judet' => 'Timiș',
        'telefon' => '0701234567',
        'email' => 'nicolae.serban@email.ro',
        'profesie' => 'Pensionar (fost lăcătuș)',
        'loc_de_munca' => '-',
        'istoric_medical' => 'Cardiopatie ischemică. By-pass aortocoronarian 2020.',
        'alergii' => 'Latex',
    ],
    10 => [
        'id' => 10,
        'id_utilizator' => 19,
        'id_medic' => 1,
        'nume' => 'Toma',
        'prenume' => 'Paula',
        'cnp' => '2510606350101',
        'varsta' => 74,
        'strada' => 'Str. Crinilor nr. 4',
        'oras' => 'Timișoara',
        'judet' => 'Timiș',
        'telefon' => '0712345678',
        'email' => 'paula.toma@email.ro',
        'profesie' => 'Pensionar',
        'loc_de_munca' => '-',
        'istoric_medical' => 'HTA. Osteoporoză cu fractură vertebrală 2023.',
        'alergii' => 'Niciuna',
    ],
    11 => [
        'id' => 11,
        'id_utilizator' => 20,
        'id_medic' => 3,
        'nume' => 'Cojocaru',
        'prenume' => 'Mihai',
        'cnp' => '1470229350112',
        'varsta' => 78,
        'strada' => 'Aleea Voievozilor nr. 11',
        'oras' => 'Timișoara',
        'judet' => 'Timiș',
        'telefon' => '0723456789',
        'email' => 'mihai.cojocaru@email.ro',
        'profesie' => 'Pensionar (fost taximetrist)',
        'loc_de_munca' => '-',
        'istoric_medical' => 'BPOC moderat. Fost fumător.',
        'alergii' => 'Polenuri',
    ],
    12 => [
        'id' => 12,
        'id_utilizator' => 21,
        'id_medic' => 2,
        'nume' => 'Barbu',
        'prenume' => 'Rodica',
        'cnp' => '2491111350123',
        'varsta' => 76,
        'strada' => 'Str. Macilor nr. 28',
        'oras' => 'Timișoara',
        'judet' => 'Timiș',
        'telefon' => '0734567890',
        'email' => 'rodica.barbu@email.ro',
        'profesie' => 'Pensionar (fost croitor)',
        'loc_de_munca' => '-',
        'istoric_medical' => 'HTA controlată. Hipercolesterolemie.',
        'alergii' => 'Niciuna',
    ],
    13 => [
        'id' => 13,
        'id_utilizator' => 22,
        'id_medic' => 1,
        'nume' => 'Olaru',
        'prenume' => 'Dumitru',
        'cnp' => '1420319350134',
        'varsta' => 82,
        'strada' => 'Str. Brândușelor nr. 6',
        'oras' => 'Timișoara',
        'judet' => 'Timiș',
        'telefon' => '0745678901',
        'email' => 'dumitru.olaru@email.ro',
        'profesie' => 'Pensionar',
        'loc_de_munca' => '-',
        'istoric_medical' => 'Stenoză aortică moderată. HTA. Diabet tip 2.',
        'alergii' => 'Statine',
    ],
    14 => [
        'id' => 14,
        'id_utilizator' => 23,
        'id_medic' => 3,
        'nume' => 'Neagu',
        'prenume' => 'Florica',
        'cnp' => '2540708350145',
        'varsta' => 71,
        'strada' => 'Str. Salciei nr. 15',
        'oras' => 'Timișoara',
        'judet' => 'Timiș',
        'telefon' => '0756789012',
        'email' => 'florica.neagu@email.ro',
        'profesie' => 'Pensionar (fost bibliotecar)',
        'loc_de_munca' => '-',
        'istoric_medical' => 'Anxietate cronică. HTA borderline.',
        'alergii' => 'Niciuna',
    ],
    15 => [
        'id' => 15,
        'id_utilizator' => 24,
        'id_medic' => 2,
        'nume' => 'Lupu',
        'prenume' => 'Ștefan',
        'cnp' => '1460502350156',
        'varsta' => 79,
        'strada' => 'Str. Plopilor nr. 22',
        'oras' => 'Timișoara',
        'judet' => 'Timiș',
        'telefon' => '0767890123',
        'email' => 'stefan.lupu@email.ro',
        'profesie' => 'Pensionar (fost mecanic)',
        'loc_de_munca' => '-',
        'istoric_medical' => 'Insuficiență cardiacă NYHA II. Diabet tip 2.',
        'alergii' => 'Iod, fructe de mare',
    ],
];

// ============================================
// PRAGURI PACIENT (din SQL-ul lui Darius)
// ============================================
$GLOBALS['MOCK_PRAGURI_PACIENT'] = [];
foreach ($GLOBALS['MOCK_PACIENT'] as $id => $p) {
    $GLOBALS['MOCK_PRAGURI_PACIENT'][$id] = [
        'id_pacient' => $id,
        'max_puls' => 93.0,
        'min_puls' => 68.0,
        'max_temp' => 38.5,
    ];
}
// Praguri personalizate pentru câțiva pacienți
$GLOBALS['MOCK_PRAGURI_PACIENT'][1]['max_puls'] = 88.0;  // Ion Popescu - cu infarct
$GLOBALS['MOCK_PRAGURI_PACIENT'][1]['min_puls'] = 55.0;
$GLOBALS['MOCK_PRAGURI_PACIENT'][9]['max_puls'] = 85.0;  // Nicolae Șerban - by-pass
$GLOBALS['MOCK_PRAGURI_PACIENT'][13]['max_puls'] = 90.0; // Dumitru Olaru - stenoză aortică

// ============================================
// CONSULTAȚII
// ============================================
$GLOBALS['MOCK_CONSULTATII'] = [
    1 => [
        'id' => 1,
        'id_pacient' => 1,
        'id_medic' => 1,
        'data_consultatie' => '2026-05-15 10:30:00',
        'motiv_prezentare' => 'Control periodic. Dispnee la efort moderat.',
        'simptome' => 'Dispnee la urcat scări (1-2 etaje), oboseală vesperală, edeme gambiere ușoare seara.',
        'diagnostic' => 'I50.9 - Insuficiență cardiacă, nespecificată; I10 - Hipertensiune esențială',
        'retete' => 'Bisoprolol 5mg 1cp/zi dimineața; Lisinopril 10mg 1cp/zi seara; Furosemid 40mg 1cp la nevoie',
        'id_recomandari' => 1,
        'trimiteri' => 'Ecocardiografie de control - Clinica Cardiomed',
    ],
    2 => [
        'id' => 2,
        'id_pacient' => 1,
        'id_medic' => 1,
        'data_consultatie' => '2026-04-10 11:00:00',
        'motiv_prezentare' => 'Verificare valori tensionale.',
        'simptome' => 'Asimptomatic. Tensiune crescută la măsurătorile de acasă (160/95 mmHg).',
        'diagnostic' => 'I10 - Hipertensiune esențială - decompensată',
        'retete' => 'Continuă schema existentă. Adăugare Amlodipină 5mg 1cp/zi.',
        'id_recomandari' => null,
        'trimiteri' => null,
    ],
    3 => [
        'id' => 3,
        'id_pacient' => 2,
        'id_medic' => 1,
        'data_consultatie' => '2026-05-20 09:00:00',
        'motiv_prezentare' => 'Palpitații frecvente.',
        'simptome' => 'Senzație de bătăi neregulate ale inimii, mai ales seara. Anxietate asociată.',
        'diagnostic' => 'I48.0 - Fibrilație atrială paroxistică',
        'retete' => 'Apixaban 5mg 2x/zi; Bisoprolol 2.5mg 1cp/zi',
        'id_recomandari' => 2,
        'trimiteri' => 'Holter ECG 24h',
    ],
    4 => [
        'id' => 4,
        'id_pacient' => 3,
        'id_medic' => 1,
        'data_consultatie' => '2026-05-18 14:00:00',
        'motiv_prezentare' => 'Tuse persistentă cu expectorație.',
        'simptome' => 'Tuse productivă matinală, dispnee progresivă la efort.',
        'diagnostic' => 'J44.1 - BPOC cu exacerbare acută',
        'retete' => 'Salbutamol spray la nevoie; Prednisolon 30mg 5 zile; Amoxicilină 500mg x3/zi 7 zile',
        'id_recomandari' => null,
        'trimiteri' => 'Spirometrie de control',
    ],
    5 => [
        'id' => 5,
        'id_pacient' => 4,
        'id_medic' => 2,
        'data_consultatie' => '2026-05-12 10:00:00',
        'motiv_prezentare' => 'Control diabet.',
        'simptome' => 'HbA1c crescut la 8.2%. Glicemii a jeun 160-180 mg/dl.',
        'diagnostic' => 'E11.9 - Diabet zaharat tip 2 decompensat',
        'retete' => 'Metformin 1000mg 2x/zi; Empagliflozin 10mg 1cp/zi',
        'id_recomandari' => 3,
        'trimiteri' => 'Examen oftalmologic; Test microalbuminurie',
    ],
    6 => [
        'id' => 6,
        'id_pacient' => 5,
        'id_medic' => 2,
        'data_consultatie' => '2026-05-22 11:30:00',
        'motiv_prezentare' => 'Dureri precordiale la efort.',
        'simptome' => 'Senzație de constrictie toracică la efort fizic moderat, cedează la repaus.',
        'diagnostic' => 'I20.9 - Angină pectorală stabilă',
        'retete' => 'Nitroglicerină sublingual la nevoie; Atorvastatină 40mg seara; Aspirina 75mg/zi',
        'id_recomandari' => 4,
        'trimiteri' => 'Test de efort EKG',
    ],
    7 => [
        'id' => 7,
        'id_pacient' => 9,
        'id_medic' => 3,
        'data_consultatie' => '2026-05-19 13:00:00',
        'motiv_prezentare' => 'Control post-bypass.',
        'simptome' => 'Stare generală bună. Cicatrice vindecată corect.',
        'diagnostic' => 'Z95.1 - Status post bypass aortocoronarian',
        'retete' => 'Continuă schema: Clopidogrel + Aspirina + Atorvastatină + Bisoprolol',
        'id_recomandari' => 5,
        'trimiteri' => null,
    ],
];

// ============================================
// RECOMANDĂRI
// ============================================
$GLOBALS['MOCK_RECOMANDARI'] = [
    1 => [
        'id_recomandare' => 1,
        'id_pacient' => 1,
        'id_medic' => 1,
        'tip_recomandare' => 'plimbare',
        'indicatii' => 'Plimbare zilnică 30 minute pe teren plat. Evitați efortul intens. Dacă apare dispnee, opriți-vă imediat.',
    ],
    2 => [
        'id_recomandare' => 2,
        'id_pacient' => 2,
        'id_medic' => 1,
        'tip_recomandare' => 'plimbare',
        'indicatii' => 'Plimbare în ritm lejer 20-30 min de 4-5 ori pe săptămână. Evitați cafeaua și alcoolul.',
    ],
    3 => [
        'id_recomandare' => 3,
        'id_pacient' => 4,
        'id_medic' => 2,
        'tip_recomandare' => 'bicicletă',
        'indicatii' => 'Bicicletă staționară 20 min/zi la intensitate moderată. Verificați glicemia înainte și după.',
    ],
    4 => [
        'id_recomandare' => 4,
        'id_pacient' => 5,
        'id_medic' => 2,
        'tip_recomandare' => 'plimbare',
        'indicatii' => 'Plimbare 30 min/zi. Aveți întotdeauna nitroglicerina la dvs. La primul semn de durere precordială - opriți efortul.',
    ],
    5 => [
        'id_recomandare' => 5,
        'id_pacient' => 9,
        'id_medic' => 3,
        'tip_recomandare' => 'exerciții fizice',
        'indicatii' => 'Program de reabilitare cardiacă: 40 min/zi exerciții moderate. Bicicletă staționară + plimbare.',
    ],
    6 => [
        'id_recomandare' => 6,
        'id_pacient' => 6,
        'id_medic' => 1,
        'tip_recomandare' => 'alergat',
        'indicatii' => 'Jogging lejer 20 min/zi de 3 ori pe săptămână. În parc, pe teren plat.',
    ],
    7 => [
        'id_recomandare' => 7,
        'id_pacient' => 10,
        'id_medic' => 1,
        'tip_recomandare' => 'plimbare',
        'indicatii' => 'Plimbare 30 min/zi. Atenție la căzături - aveți osteoporoză. Folosiți încălțăminte stabilă.',
    ],
];

// ============================================
// ACTIVITĂȚI (din SQL-ul lui Darius)
// ============================================
$today = new DateTime('today');
$GLOBALS['MOCK_ACTIVITATI'] = [];
$activityId = 1;
foreach ($GLOBALS['MOCK_RECOMANDARI'] as $rec) {
    for ($i = 0; $i < 7; $i++) {
        $date = clone $today;
        $date->modify("-{$i} days");
        $GLOBALS['MOCK_ACTIVITATI'][$activityId] = [
            'id_activitate' => $activityId,
            'id_pacient' => $rec['id_pacient'],
            'nume_activitate' => ucfirst($rec['tip_recomandare']),
            'descriere' => $rec['indicatii'],
            'data_programata' => $date->format('Y-m-d'),
            'ora_programata' => ['09:00', '10:30', '17:00'][rand(0, 2)],
            'este_finalizata' => $i > 0 ? (rand(0, 10) > 3 ? 1 : 0) : 0,
        ];
        $activityId++;
    }
}

// ============================================
// MĂSURĂTORI (date sintetice ultimele 24h pt fiecare pacient)
// ============================================
$GLOBALS['MOCK_MASURATORI'] = [];
$measureId = 1;
foreach ($GLOBALS['MOCK_PACIENT'] as $idPacient => $pacient) {
    // Pentru fiecare pacient, generăm măsurători la fiecare 30 min în ultimele 24 ore
    for ($i = 0; $i < 48; $i++) {
        $time = new DateTime('now');
        $time->modify('-' . ($i * 30) . ' minutes');
        $timestamp = $time->format('Y-m-d H:i:s');
        
        // Valori realiste cu variație ușoară
        $pulsBase = rand(65, 88);
        $tempBase = round(36.2 + (rand(0, 12) / 10), 1);
        
        $GLOBALS['MOCK_MASURATORI'][$measureId++] = [
            'id_masurare' => $measureId,
            'id_pacient' => $idPacient,
            'tip_parametru' => 'puls',
            'valoare' => $pulsBase,
            'unitate_masurata' => 'bpm',
            'moment_inregistrare' => $timestamp,
        ];
        $GLOBALS['MOCK_MASURATORI'][$measureId++] = [
            'id_masurare' => $measureId,
            'id_pacient' => $idPacient,
            'tip_parametru' => 'temperatura',
            'valoare' => $tempBase,
            'unitate_masurata' => '°C',
            'moment_inregistrare' => $timestamp,
        ];
    }
}

// ============================================
// ALARME (câteva exemple realiste)
// ============================================
$GLOBALS['MOCK_ALARME'] = [
    1 => [
        'id' => 1,
        'id_pacient' => 1,
        'tip_alarma' => 'puls',
        'valoare_declansare' => 102.0,
        'prag_minim' => 55.0,
        'prag_maxim' => 88.0,
        'moment_declansare' => '2026-05-23 18:42:15',
        'durata_persistenta' => 45,
        'mesaj' => 'Puls crescut detectat. Pacientul a raportat efort fizic intens.',
    ],
    2 => [
        'id' => 2,
        'id_pacient' => 3,
        'tip_alarma' => 'temperatura',
        'valoare_declansare' => 38.9,
        'prag_minim' => 35.0,
        'prag_maxim' => 38.5,
        'moment_declansare' => '2026-05-23 22:15:30',
        'durata_persistenta' => 120,
        'mesaj' => 'Temperatură crescută. Posibilă infecție respiratorie.',
    ],
    3 => [
        'id' => 3,
        'id_pacient' => 5,
        'tip_alarma' => 'puls',
        'valoare_declansare' => 110.0,
        'prag_minim' => 60.0,
        'prag_maxim' => 95.0,
        'moment_declansare' => '2026-05-24 09:30:00',
        'durata_persistenta' => 60,
        'mesaj' => 'Tahicardie. Pacient a luat nitroglicerină.',
    ],
    4 => [
        'id' => 4,
        'id_pacient' => 7,
        'tip_alarma' => 'temperatura',
        'valoare_declansare' => 38.7,
        'prag_minim' => 35.5,
        'prag_maxim' => 38.5,
        'moment_declansare' => '2026-05-24 07:15:00',
        'durata_persistenta' => 30,
        'mesaj' => 'Febră detectată. De investigat posibilă infecție.',
    ],
    5 => [
        'id' => 5,
        'id_pacient' => 9,
        'tip_alarma' => 'puls',
        'valoare_declansare' => 50.0,
        'prag_minim' => 55.0,
        'prag_maxim' => 85.0,
        'moment_declansare' => '2026-05-24 03:20:00',
        'durata_persistenta' => 90,
        'mesaj' => 'Bradicardie nocturnă. Status post-bypass.',
    ],
];

// ============================================
// DISPOZITIVE
// ============================================
$GLOBALS['MOCK_DISPOZITIVE'] = [];
$devId = 1;
foreach ($GLOBALS['MOCK_PACIENT'] as $idPacient => $pacient) {
    // Smartphone
    $GLOBALS['MOCK_DISPOZITIVE'][$devId] = [
        'id' => $devId,
        'tip_dispozitiv' => 'smartphone',
        'id_pacient' => $idPacient,
        'stare' => rand(0, 10) > 1 ? 'activ' : 'inactiv',
        'detalii' => 'Samsung Galaxy A54, Android 14, App v1.0',
    ];
    $devId++;
    // Senzor ESP32
    $GLOBALS['MOCK_DISPOZITIVE'][$devId] = [
        'id' => $devId,
        'tip_dispozitiv' => 'senzor',
        'id_pacient' => $idPacient,
        'stare' => rand(0, 10) > 1 ? 'activ' : 'inactiv',
        'detalii' => 'ESP32 + MAX30100 (puls) + DHT11 (temp/umiditate) + AD8232 (ECG)',
    ];
    $devId++;
}

// ============================================
// MESAJE HL7 (presupunem că tabela va exista; întrebăm Roxana)
// ============================================
$GLOBALS['MOCK_MESAJE_HL7'] = [
    1 => [
        'id' => 1,
        'tip_mesaj' => 'HL7 v2.5 - Trimitere consult',
        'sursa' => 'Dr. Popescu Adrian - Medic familie - CMI Aradul Nou',
        'destinatie' => 'Dr. Popescu Andrei - Cardiolog',
        'continut' => "MSH|^~\\&|MEDFAM|CMI|VITALCARES|CLINICA|20260520102500||REF^I12|MSG001|P|2.5\nPID|||PACIENT001||Popescu^Ion||19450203|M\nPRD|RP|Popescu^Adrian^^^Dr|Medic Familie\nPRD|RT|Popescu^Andrei^^^Dr|Cardiolog\nPID|||PACIENT001\nDG1|1||I50.9^Insuficiență cardiacă^ICD10|Insuficiență cardiacă|20260520",
        'moment_transmitere' => '2026-05-20 10:25:00',
        'id_pacient' => 1,
    ],
    2 => [
        'id' => 2,
        'tip_mesaj' => 'HL7 FHIR - Scrisoare medicală',
        'sursa' => 'Dr. Popescu Andrei - Cardiolog',
        'destinatie' => 'Dr. Popescu Adrian - Medic familie',
        'continut' => '{"resourceType":"Composition","status":"final","type":{"text":"Scrisoare medicală"},"subject":{"reference":"Patient/1"},"author":[{"reference":"Practitioner/1"}],"date":"2026-05-22","section":[{"title":"Diagnostic","text":"Insuficiență cardiacă NYHA II"},{"title":"Recomandări","text":"Continuă tratamentul..."}]}',
        'moment_transmitere' => '2026-05-22 16:45:00',
        'id_pacient' => 1,
    ],
];

// ============================================
// AUDIT LOG (gol la început)
// ============================================
$GLOBALS['MOCK_AUDIT'] = [];

// ============================================
// ICD-10 codes uzuale (pentru consultații)
// ============================================
$GLOBALS['MOCK_ICD10'] = [
    'I10' => 'Hipertensiune esențială',
    'I11.0' => 'Boală cardiacă hipertensivă cu insuficiență',
    'I20.0' => 'Angină pectorală instabilă',
    'I20.9' => 'Angină pectorală stabilă',
    'I21.9' => 'Infarct miocardic acut',
    'I25.1' => 'Boală cardiacă aterosclerotică',
    'I48.0' => 'Fibrilație atrială paroxistică',
    'I48.9' => 'Fibrilație atrială',
    'I50.0' => 'Insuficiență cardiacă congestivă',
    'I50.9' => 'Insuficiență cardiacă, nespecificată',
    'E11.9' => 'Diabet zaharat tip 2, fără complicații',
    'E11.6' => 'Diabet zaharat tip 2 cu complicații',
    'E78.5' => 'Hiperlipidemie',
    'J44.1' => 'BPOC cu exacerbare acută',
    'J44.9' => 'BPOC, nespecificată',
    'M81.0' => 'Osteoporoză postmenopauză',
    'M19.9' => 'Artroză, nespecificată',
    'N18.3' => 'Boală renală cronică stadiul 3',
    'F32.9' => 'Episod depresiv',
    'F41.1' => 'Anxietate generalizată',
    'Z95.1' => 'Status post bypass aortocoronarian',
    'Z95.5' => 'Status post angioplastie cu stent',
];