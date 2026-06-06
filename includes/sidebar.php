<?php
/**
 * Sidebar lateral - meniu navigare
 * Icoane SVG inline, curate și profesionale
 */
$_active = $GLOBALS['_active_menu'] ?? '';
$_user = currentUser();
$_role = $_user['rol'] ?? '';

// Icoane SVG (Lucide-style, 24x24 viewBox)
function ico($name) {
    $icons = [
        'dashboard'    => '<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
        'users'        => '<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>',
        'user-plus'    => '<path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/>',
        'clipboard'    => '<path d="M16 4h2a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>',
        'pill'         => '<path d="M10.5 1.5l-8 8a4.95 4.95 0 007 7l8-8a4.95 4.95 0 00-7-7z"/><line x1="8.5" y1="8.5" x2="15.5" y2="15.5"/>',
        'check-square' => '<polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>',
        'activity'     => '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>',
        'alert'        => '<path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
        'smartphone'   => '<rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/>',
        'mail'         => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22 6 12 13 2 6"/>',
        'bar-chart'    => '<line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/><line x1="6" y1="20" x2="6" y2="16"/>',
        'file-text'    => '<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
        'stethoscope'  => '<path d="M4.8 2.65l-.04.93c-.09 1.86.5 3.55 1.66 4.71A5.48 5.48 0 0010.36 10h.93"/><path d="M19.2 2.65l.04.93c.09 1.86-.5 3.55-1.66 4.71A5.48 5.48 0 0113.64 10h-.93"/><circle cx="12" cy="12" r="2"/><path d="M12 14v4a2 2 0 104 0"/><circle cx="16" cy="18" r="2"/>',
        'search'       => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
        'archive'      => '<polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/>',
        'history'      => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        'user'         => '<path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>',
    ];
    $path = $icons[$name] ?? '';
    return '<span class="icon"><svg viewBox="0 0 24 24">' . $path . '</svg></span>';
}
?>
<aside class="app-sidebar">
    
<?php if ($_role === 'medic'): ?>
    
    <div class="menu-section">
        <div class="menu-section-title">Principal</div>
        <a href="<?= url('dashboard_medic.php') ?>" 
           class="menu-item <?= activeIf($_active, 'dashboard') ?>">
            <?= ico('dashboard') ?> Dashboard
        </a>
    </div>
    
    <div class="menu-section">
        <div class="menu-section-title">Pacienți</div>
        <a href="<?= url('pacienti.php') ?>" 
           class="menu-item <?= activeIf($_active, 'pacienti') ?>">
            <?= ico('users') ?> Listă pacienți
        </a>
        <a href="<?= url('pacient_adauga.php') ?>" 
           class="menu-item <?= activeIf($_active, 'pacient_adauga') ?>">
            <?= ico('user-plus') ?> Adaugă pacient
        </a>
    </div>
    
    <div class="menu-section">
        <div class="menu-section-title">Activitate medicală</div>
        <a href="<?= url('consultatii.php') ?>" 
           class="menu-item <?= activeIf($_active, 'consultatii') ?>">
            <?= ico('clipboard') ?> Consultații
        </a>
        <a href="<?= url('recomandari.php') ?>" 
           class="menu-item <?= activeIf($_active, 'recomandari') ?>">
            <?= ico('pill') ?> Recomandări
        </a>
        <a href="<?= url('activitati.php') ?>" 
           class="menu-item <?= activeIf($_active, 'activitati') ?>">
            <?= ico('check-square') ?> Activități
        </a>
    </div>
    
    <div class="menu-section">
        <div class="menu-section-title">Monitorizare</div>
        <a href="<?= url('monitorizare.php') ?>" 
           class="menu-item <?= activeIf($_active, 'monitorizare') ?>">
            <?= ico('activity') ?> Monitorizare live
        </a>
        <a href="<?= url('alarme.php') ?>" 
           class="menu-item <?= activeIf($_active, 'alarme') ?>">
            <?= ico('alert') ?> Alarme
        </a>
    </div>
    
    <div class="menu-section">
        <div class="menu-section-title">Comunicare</div>
        <a href="<?= url('mesaje_hl7.php') ?>" 
           class="menu-item <?= activeIf($_active, 'mesaje_hl7') ?>">
            <?= ico('mail') ?> Mesaje HL7
        </a>
    </div>
    
    <div class="menu-section">
        <div class="menu-section-title">Rapoarte</div>
        <a href="<?= url('statistici.php') ?>" 
           class="menu-item <?= activeIf($_active, 'statistici') ?>">
            <?= ico('bar-chart') ?> Statistici
        </a>
        <a href="<?= url('rapoarte.php') ?>" 
           class="menu-item <?= activeIf($_active, 'rapoarte') ?>">
            <?= ico('file-text') ?> Rapoarte
        </a>
    </div>
    
    <div class="menu-section">
        <div class="menu-section-title">Sistem</div>
        <a href="<?= url('doctori.php') ?>" 
           class="menu-item <?= activeIf($_active, 'doctori') ?>">
            <?= ico('stethoscope') ?> Medici
        </a>
        <a href="<?= url('audit_log.php') ?>" 
           class="menu-item <?= activeIf($_active, 'audit_log') ?>">
            <?= ico('search') ?> Audit log
        </a>
        <a href="<?= url('arhiva.php') ?>" 
           class="menu-item <?= activeIf($_active, 'arhiva') ?>">
            <?= ico('archive') ?> Arhivă
        </a>
        <a href="<?= url('versiuni.php') ?>" 
           class="menu-item <?= activeIf($_active, 'versiuni') ?>">
            <?= ico('history') ?> Istoric versiuni
        </a>
    </div>

<?php elseif ($_role === 'pacient'): ?>
    
    <div class="menu-section">
        <div class="menu-section-title">Principal</div>
        <a href="<?= url('dashboard_pacient.php') ?>" 
           class="menu-item <?= activeIf($_active, 'dashboard') ?>">
            <?= ico('dashboard') ?> Dashboard
        </a>
        <a href="<?= url('profil_pacient.php') ?>" 
           class="menu-item <?= activeIf($_active, 'profil_pacient') ?>">
            <?= ico('user') ?> Fișa mea
        </a>
    </div>
    
    <div class="menu-section">
        <div class="menu-section-title">Sănătate</div>
        <a href="<?= url('monitorizare.php') ?>" 
           class="menu-item <?= activeIf($_active, 'monitorizare') ?>">
            <?= ico('activity') ?> Monitorizare
        </a>
        <a href="<?= url('alarme.php') ?>" 
           class="menu-item <?= activeIf($_active, 'alarme') ?>">
            <?= ico('alert') ?> Alarme
        </a>
    </div>
    
    <div class="menu-section">
        <div class="menu-section-title">Recomandări</div>
        <a href="<?= url('recomandari.php') ?>" 
           class="menu-item <?= activeIf($_active, 'recomandari') ?>">
            <?= ico('pill') ?> Recomandări
        </a>
        <a href="<?= url('activitati.php') ?>" 
           class="menu-item <?= activeIf($_active, 'activitati') ?>">
            <?= ico('check-square') ?> Activitățile mele
        </a>
    </div>
    
    <div class="menu-section">
        <div class="menu-section-title">Istoric</div>
        <a href="<?= url('consultatii.php') ?>" 
           class="menu-item <?= activeIf($_active, 'consultatii') ?>">
            <?= ico('clipboard') ?> Consultații
        </a>
    </div>

<?php endif; ?>
    
</aside>

<main class="app-main">
    <?php // De aici începe conținutul propriu al fiecărei pagini ?>