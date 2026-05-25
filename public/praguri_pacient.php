<?php
require_once __DIR__ . '/../app/config.php';
requireRole('medic');

$idPacient = (int)($_GET['id'] ?? 0);
$pacient = PacientRepo::findById($idPacient);

if (!$pacient || !medicCanAccessPacient($idPacient)) {
    flash('error', 'Pacient negăsit sau acces interzis.');
    redirect(url('pacienti.php'));
}

$praguri = PraguriRepo::findByPacient($idPacient);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    
    $data = [
        'max_puls' => (float)$_POST['max_puls'],
        'min_puls' => (float)$_POST['min_puls'],
        'max_temp' => (float)$_POST['max_temp'],
    ];
    
    // Validări simple
    $errors = [];
    if ($data['min_puls'] >= $data['max_puls']) {
        $errors[] = 'Pulsul minim trebuie să fie mai mic decât maxim.';
    }
    if ($data['max_temp'] < 35 || $data['max_temp'] > 42) {
        $errors[] = 'Temperatura maximă trebuie între 35 și 42°C.';
    }
    
    if (empty($errors)) {
        if (PraguriRepo::upsert($idPacient, $data)) {
            logCurrentUserAction('UPDATE', 'PraguriPacient', $idPacient, 
                'Modificare praguri: ' . json_encode($data));
            flash('success', 'Pragurile au fost actualizate. Vor fi sincronizate cu dispozitivul pacientului.');
            redirect(url('pacient_detalii.php?id=' . $idPacient));
        } else {
            flash('error', 'Eroare la salvare.');
        }
    } else {
        foreach ($errors as $err) flash('error', $err);
    }
    
    // Repopulare valori cu cele introduse
    $praguri = array_merge($praguri, $data);
}

renderHeader('Praguri: ' . PacientRepo::fullName($pacient), 'pacienti');
renderFlash();
?>

<div class="page-header">
    <div>
        <div class="breadcrumb">
            <a href="<?= url('pacienti.php') ?>">Pacienți</a> / 
            <a href="<?= url('pacient_detalii.php?id=' . $idPacient) ?>"><?= e(PacientRepo::fullName($pacient)) ?></a> /
            Praguri
        </div>
        <h1>Praguri de monitorizare</h1>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <p class="text-muted mb-4">
            Aceste praguri sunt folosite de dispozitivul pacientului pentru a genera alarme automate.
            Modificările vor fi sincronizate la următoarea conexiune a smartphone-ului cu cloud-ul.
        </p>
        
        <form method="POST" action="">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrfToken() ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">❤ Puls minim (bpm)</label>
                    <input type="number" name="min_puls" class="form-control" 
                           value="<?= e($praguri['min_puls']) ?>" step="0.1" min="30" max="150" required>
                    <div class="form-help">Sub această valoare se declanșează alarmă de bradicardie</div>
                </div>
                <div class="form-group">
                    <label class="form-label">❤ Puls maxim (bpm)</label>
                    <input type="number" name="max_puls" class="form-control" 
                           value="<?= e($praguri['max_puls']) ?>" step="0.1" min="50" max="200" required>
                    <div class="form-help">Peste această valoare se declanșează alarmă de tahicardie</div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">🌡 Temperatură maximă (°C)</label>
                    <input type="number" name="max_temp" class="form-control" 
                           value="<?= e($praguri['max_temp']) ?>" step="0.1" min="35" max="42" required>
                    <div class="form-help">Peste această valoare se declanșează alarmă de febră</div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg">Salvează pragurile</button>
                <a href="<?= url('pacient_detalii.php?id=' . $idPacient) ?>" class="btn">Renunță</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3>ℹ Valori de referință</h3></div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr><th>Parametru</th><th>Adult normal</th><th>Vârstnic (peste 65)</th><th>Implicit sistem</th></tr>
            </thead>
            <tbody>
                <tr><td>Puls</td><td>60-100 bpm</td><td>60-90 bpm</td><td>68-93 bpm</td></tr>
                <tr><td>Temperatură</td><td>36.1-37.2°C</td><td>36.0-37.0°C</td><td>max 38.5°C</td></tr>
            </tbody>
        </table>
    </div>
</div>

<?php renderFooter(); ?>