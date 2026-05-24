/**
 * Vital Cares - JavaScript principal
 * Interacțiuni de bază: confirmări, validări form
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // === Confirmare ștergere (data-confirm) ===
    // Folosire: <a href="..." data-confirm="Sigur ștergi?">Șterge</a>
    document.querySelectorAll('[data-confirm]').forEach(function(el) {
        el.addEventListener('click', function(e) {
            var msg = el.getAttribute('data-confirm') || 'Sigur dorești această acțiune?';
            if (!confirm(msg)) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    });
    
    // === Auto-hide flash messages după 5 secunde ===
    document.querySelectorAll('.flash').forEach(function(el) {
        setTimeout(function() {
            el.style.transition = 'opacity 0.5s';
            el.style.opacity = '0';
            setTimeout(function() { el.style.display = 'none'; }, 500);
        }, 5000);
    });
    
    // === Validare basic CNP la blur ===
    document.querySelectorAll('input[data-validate="cnp"]').forEach(function(input) {
        input.addEventListener('blur', function() {
            var val = input.value.trim();
            var err = input.parentNode.querySelector('.form-error.cnp-error');
            if (val && !/^[1-9]\d{12}$/.test(val)) {
                if (!err) {
                    err = document.createElement('div');
                    err.className = 'form-error cnp-error';
                    err.textContent = 'CNP invalid (13 cifre, prima diferită de 0)';
                    input.parentNode.appendChild(err);
                }
            } else if (err) {
                err.remove();
            }
        });
    });
    
    // === Validare email la blur ===
    document.querySelectorAll('input[type="email"]').forEach(function(input) {
        input.addEventListener('blur', function() {
            var val = input.value.trim();
            var err = input.parentNode.querySelector('.form-error.email-error');
            if (val && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
                if (!err) {
                    err = document.createElement('div');
                    err.className = 'form-error email-error';
                    err.textContent = 'Adresă de email invalidă';
                    input.parentNode.appendChild(err);
                }
            } else if (err) {
                err.remove();
            }
        });
    });
    
    // === Toggle parolă vizibilă ===
    document.querySelectorAll('[data-toggle-password]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var target = document.querySelector(btn.getAttribute('data-toggle-password'));
            if (target) {
                target.type = target.type === 'password' ? 'text' : 'password';
            }
        });
    });
    
});