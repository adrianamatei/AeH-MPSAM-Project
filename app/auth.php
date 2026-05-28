<?php
/**
 * Funcții de autentificare și gestiune sesiune
 * 
 * Criterii EuroRec: GS001512.1, GS002211.1, GS002655.2, GS001947.2
 * 
 * NOTĂ AZURE: Utilizatori.id = PK, Medic nu are id_utilizator,
 *             Pacient are id_utilizator (FK)
 */

function login($email, $password) {
    $user = UtilizatorRepo::findByEmail($email);
    if (!$user) return null;
    
    // Verifică parola — în Azure parolele pot fi plain text sau hash
    $passwordOk = false;
    if (password_get_info($user['parola'])['algo'] !== null && password_get_info($user['parola'])['algo'] !== 0) {
        // Parolă hash bcrypt
        $passwordOk = password_verify($password, $user['parola']);
    } else {
        // Parolă plain text (temporar, pentru dezvoltare)
        $passwordOk = ($user['parola'] === $password);
    }
    
    if (!$passwordOk) return null;
    
    // Salvează datele esențiale în sesiune
    $_SESSION['user'] = [
        'id_utilizator' => $user['id'],
        'email' => $user['email'],
        'rol' => $user['rol'],
    ];
    $_SESSION['last_activity'] = time();
    
    // Atașează profilul (medic sau pacient) după rol
    if ($user['rol'] === 'medic') {
        // Azure: Medic nu are id_utilizator — căutăm medic cu id_medic = user.id
        // SAU prin altă logică (depinde de cum a configurat Roxana)
        // Temporar: id_medic = id utilizator (de adaptat)
        $medic = MedicRepo::findById($user['id']);
        if ($medic) {
            $_SESSION['user']['id_profil'] = $medic['id'] ?? $medic['id_medic'];
            $_SESSION['user']['nume_complet'] = trim(($medic['nume'] ?? '') . ' ' . ($medic['prenume'] ?? ''));
        }
    } elseif ($user['rol'] === 'pacient') {
        // Pacient are id_utilizator ca FK
        $pacient = PacientRepo::findByUtilizator($user['id']);
        if ($pacient) {
            $_SESSION['user']['id_profil'] = $pacient['id'];
            $_SESSION['user']['nume_complet'] = trim(($pacient['nume'] ?? '') . ' ' . ($pacient['prenume'] ?? ''));
        }
    }
    
    logAction($user['id'], 'LOGIN', 'Utilizatori', $user['id'], 'Login reușit');
    session_regenerate_id(true);
    
    return $_SESSION['user'];
}

function logout() {
    if (isLoggedIn()) {
        $userId = $_SESSION['user']['id_utilizator'];
        logAction($userId, 'LOGOUT', 'Utilizatori', $userId, 'Logout');
    }
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function isLoggedIn() {
    if (!isset($_SESSION['user'])) return false;
    if (isset($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
        logout();
        return false;
    }
    $_SESSION['last_activity'] = time();
    return true;
}

function currentUser() { return isLoggedIn() ? $_SESSION['user'] : null; }
function hasRole($role) { $u = currentUser(); return $u && $u['rol'] === $role; }
function hasAnyRole(array $roles) { $u = currentUser(); return $u && in_array($u['rol'], $roles); }

function changePassword($currentPassword, $newPassword) {
    $user = currentUser();
    if (!$user) return false;
    $dbUser = UtilizatorRepo::findById($user['id_utilizator']);
    if (!$dbUser) return false;
    
    // Verifică parola curentă (hash sau plain)
    $passwordOk = false;
    if (password_get_info($dbUser['parola'])['algo'] !== null && password_get_info($dbUser['parola'])['algo'] !== 0) {
        $passwordOk = password_verify($currentPassword, $dbUser['parola']);
    } else {
        $passwordOk = ($dbUser['parola'] === $currentPassword);
    }
    if (!$passwordOk) return false;
    
    $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
    $ok = UtilizatorRepo::updatePassword($user['id_utilizator'], $newHash);
    if ($ok) logAction($user['id_utilizator'], 'CHANGE_PASSWORD', 'Utilizatori', $user['id_utilizator'], 'Schimbare parolă');
    return $ok;
}

function currentMedicId() {
    $u = currentUser();
    return ($u && $u['rol'] === 'medic') ? ($u['id_profil'] ?? null) : null;
}

function currentPacientId() {
    $u = currentUser();
    return ($u && $u['rol'] === 'pacient') ? ($u['id_profil'] ?? null) : null;
}
