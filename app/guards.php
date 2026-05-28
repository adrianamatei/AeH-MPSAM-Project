<?php
/**
 * Funcții de protecție pagini
 * 
 * Folosire în pagini: pune AT THE TOP după include config:
 *   require_once __DIR__ . '/../app/config.php';
 *   requireLogin();
 *   requireRole('medic');
 * 
 * Criterii EuroRec acoperite:
 * - GS001947.2: Acces doar utilizatori autorizați
 * - GS002175.2: Politici de gestiune acces
 * - GS002415.4: Drepturi acces după rol
 */

/**
 * Forțează utilizatorul să fie logat
 * Redirect la login dacă nu e autentificat
 */
function requireLogin() {
    if (!isLoggedIn()) {
        flash('warning', 'Trebuie să fii autentificat pentru a accesa această pagină.');
        $_SESSION['_redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? null;
        redirect(url('login.php'));
    }
}

/**
 * Forțează utilizatorul să aibă un anumit rol
 */
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        flash('error', 'Nu aveți permisiunea de a accesa această pagină.');
        // Redirect către dashboard-ul propriu
        $user = currentUser();
        if ($user['rol'] === 'medic') {
            redirect(url('dashboard_medic.php'));
        } else {
            redirect(url('dashboard_pacient.php'));
        }
    }
}

/**
 * Forțează unul din mai multe roluri
 */
function requireAnyRole(array $roles) {
    requireLogin();
    if (!hasAnyRole($roles)) {
        flash('error', 'Nu aveți permisiunea de a accesa această pagină.');
        redirect(url('index.php'));
    }
}

/**
 * Verifică dacă medicul curent are acces la datele unui pacient
 * (un medic vede doar pacienții lui)
 */
function medicCanAccessPacient($idPacient) {
    if (!hasRole('medic')) return false;
    
    $pacient = PacientRepo::findById($idPacient);
    if (!$pacient) return false;
    
    return $pacient['id_medic'] == currentMedicId();
}

/**
 * Verifică dacă pacientul curent încearcă să-și acceseze propriile date
 */
function pacientCanAccessSelf($idPacient) {
    if (!hasRole('pacient')) return false;
    return currentPacientId() == $idPacient;
}

/**
 * Verifică acces general la un pacient (medic propriu sau pacient însuși)
 * Întrerupe execuția cu redirect dacă nu are acces
 */
function requireAccessToPacient($idPacient) {
    requireLogin();
    
    $ok = medicCanAccessPacient($idPacient) || pacientCanAccessSelf($idPacient);
    
    if (!$ok) {
        flash('error', 'Nu aveți acces la datele acestui pacient.');
        $user = currentUser();
        if ($user['rol'] === 'medic') {
            redirect(url('pacienti.php'));
        } else {
            redirect(url('dashboard_pacient.php'));
        }
    }
}

/**
 * Verifică token CSRF la requesturi POST
 * Întrerupe execuția dacă token-ul lipsește sau e invalid
 */
function requireCsrf() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    
    if (!csrfVerify()) {
        flash('error', 'Sesiune invalidă. Reîncearcă.');
        redirect($_SERVER['HTTP_REFERER'] ?? url('index.php'));
    }
}
