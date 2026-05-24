<?php
/**
 * Sidebar lateral - meniu navigare
 * Conținut diferit pentru medic vs pacient
 * 
 * Variabilă disponibilă: $GLOBALS['_active_menu']
 */
$_active = $GLOBALS['_active_menu'] ?? '';
$_user = currentUser();
$_role = $_user['rol'] ?? '';
?>
<aside class="app-sidebar">
    
<?php if ($_role === 'medic'): ?>
    
    <!-- MENIU MEDIC -->
    
    <div class="menu-section">
        <div class="menu-section-title">Principal</div>
        <a href="<?= url('dashboard_medic.php') ?>" 
           class="menu-item <?= activeIf($_active, 'dashboard') ?>">
            <span class="icon">⊞</span> Dashboard
        </a>
    </div>
    
    <div class="menu-section">
        <div class="menu-section-title">Pacienți</div>
        <a href="<?= url('pacienti.php') ?>" 
           class="menu-item <?= activeIf($_active, 'pacienti') ?>">
            <span class="icon">👤</span> Listă pacienți
        </a>
        <a href="<?= url('pacient_adauga.php') ?>" 
           class="menu-item <?= activeIf($_active, 'pacient_adauga') ?>">
            <span class="icon">+</span> Adaugă pacient
        </a>
    </div>
    
    <div class="menu-section">
        <div class="menu-section-title">Activitate medicală</div>
        <a href="<?= url('consultatii.php') ?>" 
           class="menu-item <?= activeIf($_active, 'consultatii') ?>">
            <span class="icon">📋</span> Consultații
        </a>
        <a href="<?= url('recomandari.php') ?>" 
           class="menu-item <?= activeIf($_active, 'recomandari') ?>">
            <span class="icon">💊</span> Recomandări
        </a>
    </div>
    
    <div class="menu-section">
        <div class="menu-section-title">Monitorizare</div>
        <a href="<?= url('monitorizare.php') ?>" 
           class="menu-item <?= activeIf($_active, 'monitorizare') ?>">
            <span class="icon">📊</span> Monitorizare live
        </a>
        <a href="<?= url('alarme.php') ?>" 
           class="menu-item <?= activeIf($_active, 'alarme') ?>">
            <span class="icon">⚠</span> Alarme
        </a>
        <a href="<?= url('dispozitive.php') ?>" 
           class="menu-item <?= activeIf($_active, 'dispozitive') ?>">
            <span class="icon">📱</span> Dispozitive
        </a>
    </div>
    
    <div class="menu-section">
        <div class="menu-section-title">Comunicare</div>
        <a href="<?= url('mesaje_hl7.php') ?>" 
           class="menu-item <?= activeIf($_active, 'mesaje_hl7') ?>">
            <span class="icon">✉</span> Mesaje HL7
        </a>
    </div>
    
    <div class="menu-section">
        <div class="menu-section-title">Rapoarte</div>
        <a href="<?= url('statistici.php') ?>" 
           class="menu-item <?= activeIf($_active, 'statistici') ?>">
            <span class="icon">📈</span> Statistici
        </a>
        <a href="<?= url('rapoarte.php') ?>" 
           class="menu-item <?= activeIf($_active, 'rapoarte') ?>">
            <span class="icon">📄</span> Rapoarte
        </a>
    </div>
    
    <div class="menu-section">
        <div class="menu-section-title">Sistem</div>
        <a href="<?= url('doctori.php') ?>" 
           class="menu-item <?= activeIf($_active, 'doctori') ?>">
            <span class="icon">👨‍⚕</span> Medici
        </a>
        <a href="<?= url('audit_log.php') ?>" 
           class="menu-item <?= activeIf($_active, 'audit_log') ?>">
            <span class="icon">🔍</span> Audit log
        </a>
    </div>

<?php elseif ($_role === 'pacient'): ?>
    
    <!-- MENIU PACIENT -->
    
    <div class="menu-section">
        <div class="menu-section-title">Principal</div>
        <a href="<?= url('dashboard_pacient.php') ?>" 
           class="menu-item <?= activeIf($_active, 'dashboard') ?>">
            <span class="icon">⊞</span> Dashboard
        </a>
        <a href="<?= url('profil_pacient.php') ?>" 
           class="menu-item <?= activeIf($_active, 'profil_pacient') ?>">
            <span class="icon">👤</span> Fișa mea
        </a>
    </div>
    
    <div class="menu-section">
        <div class="menu-section-title">Sănătate</div>
        <a href="<?= url('monitorizare.php') ?>" 
           class="menu-item <?= activeIf($_active, 'monitorizare') ?>">
            <span class="icon">📊</span> Monitorizare
        </a>
        <a href="<?= url('alarme.php') ?>" 
           class="menu-item <?= activeIf($_active, 'alarme') ?>">
            <span class="icon">⚠</span> Alarme
        </a>
    </div>
    
    <div class="menu-section">
        <div class="menu-section-title">Recomandări</div>
        <a href="<?= url('recomandari.php') ?>" 
           class="menu-item <?= activeIf($_active, 'recomandari') ?>">
            <span class="icon">💊</span> Recomandări
        </a>
        <a href="<?= url('activitati.php') ?>" 
           class="menu-item <?= activeIf($_active, 'activitati') ?>">
            <span class="icon">✅</span> Activitățile mele
        </a>
    </div>
    
    <div class="menu-section">
        <div class="menu-section-title">Istoric</div>
        <a href="<?= url('consultatii.php') ?>" 
           class="menu-item <?= activeIf($_active, 'consultatii') ?>">
            <span class="icon">📋</span> Consultații
        </a>
    </div>

<?php endif; ?>
    
</aside>

<main class="app-main">
    <?php // De aici începe conținutul propriu al fiecărei pagini ?>