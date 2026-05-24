<?php
/**
 * Funcții de autentificare și gestiune sesiune
 * 
 * Roluri suportate: 'medic', 'pacient'
 * 
 * Criterii EuroRec acoperite:
 * - GS001512.1: Legătură rol-utilizator
 * - GS002211.1: Schimbare parolă
 * - GS002655.2: Session timeout
 * - GS001947.2: Acces doar utilizatori autorizați
 */

/**
 * Autentifică un utilizator
 * @return array|null - user data sau null dacă login eșuează
 */
function login($email, $password) {
    // Caută utilizatorul prin email
    $user = UtilizatorRepo::findByEmail($email);
    
    if (!$user) {
        return null;
    }
    
    // Verifică parola hash
    if (!password_verify($password, $user['parola'])) {
        return null;
    }
    
    // Salvează datele esențiale în sesiune
    $_SESSION['user'] = [
        'id_utilizator' => $user['id_utilizator'],
        'email' => $user['email'],
        'rol' => $user['rol'],
    ];
    $_SESSION['last_activity'] = time();
    
    // Atașează profilul (medic sau pacient) după rol
    if ($user['rol'] === 'medic') {
        $medic = MedicRepo::findByUtilizator($user['id_utilizator']);
        if ($medic) {
            $_SESSION['user']['id_profil'] = $medic['id'];
            $_SESSION['user']['nume_complet'] = trim(($medic['nume'] ?? '') . ' ' . ($medic['prenume'] ?? ''));
        }
    } elseif ($user['rol'] === 'pacient') {
        $pacient = PacientRepo::findByUtilizator($user['id_utilizator']);
        if ($pacient) {
            $_SESSION['user']['id_profil'] = $pacient['id'];
            $_SESSION['user']['nume_complet'] = trim(($pacient['nume'] ?? '') . ' ' . ($pacient['prenume'] ?? ''));
        }
    }
    
    // Audit log
    logAction($user['id_utilizator'], 'LOGIN', 'Utilizatori', $user['id_utilizator'], 'Login reușit');
    
    // Regenerează ID sesiune pt securitate
    session_regenerate_id(true);
    
    return $_SESSION['user'];
}

/**
 * Distruge sesiunea (logout)
 */
function logout() {
    if (isLoggedIn()) {
        $userId = $_SESSION['user']['id_utilizator'];
        logAction($userId, 'LOGOUT', 'Utilizatori', $userId, 'Logout');
    }
    
    $_SESSION = [];
    
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    
    session_destroy();
}

/**
 * Verifică dacă utilizatorul este autentificat
 * Verifică și timeout-ul sesiunii (criteriu EuroRec)
 */
function isLoggedIn() {
    if (!isset($_SESSION['user'])) {
        return false;
    }
    
    // Verificare timeout
    if (isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
            logout();
            return false;
        }
    }
    
    // Reset timer la fiecare request
    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * Returnează datele utilizatorului curent
 */
function currentUser() {
    return isLoggedIn() ? $_SESSION['user'] : null;
}

/**
 * Verifică dacă utilizatorul are un anumit rol
 */
function hasRole($role) {
    $user = currentUser();
    return $user && $user['rol'] === $role;
}

/**
 * Verifică dacă utilizatorul are unul din mai multe roluri
 */
function hasAnyRole(array $roles) {
    $user = currentUser();
    return $user && in_array($user['rol'], $roles);
}

/**
 * Schimbă parola utilizatorului curent
 * @return bool - succes/eșec
 */
function changePassword($currentPassword, $newPassword) {
    $user = currentUser();
    if (!$user) return false;
    
    // Verifică parola actuală
    $dbUser = UtilizatorRepo::findById($user['id_utilizator']);
    if (!$dbUser || !password_verify($currentPassword, $dbUser['parola'])) {
        return false;
    }
    
    // Hash parola nouă
    $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
    
    $ok = UtilizatorRepo::updatePassword($user['id_utilizator'], $newHash);
    
    if ($ok) {
        logAction($user['id_utilizator'], 'CHANGE_PASSWORD', 'Utilizatori', $user['id_utilizator'], 'Schimbare parolă');
    }
    
    return $ok;
}

/**
 * Helper - returnează ID-ul medic-ului curent (dacă rol=medic)
 */
function currentMedicId() {
    $user = currentUser();
    return ($user && $user['rol'] === 'medic') ? ($user['id_profil'] ?? null) : null;
}

/**
 * Helper - returnează ID-ul pacientului curent (dacă rol=pacient)
 */
function currentPacientId() {
    $user = currentUser();
    return ($user && $user['rol'] === 'pacient') ? ($user['id_profil'] ?? null) : null;
}