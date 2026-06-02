/**
 * Vital Cares - JavaScript principal
 * Interacțiuni de bază: confirmări, validări form
 */

document.addEventListener('DOMContentLoaded', function () {

    // === Confirmare ștergere (data-confirm) ===
    // Folosire: <a href="..." data-confirm="Sigur ștergi?">Șterge</a>
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            var msg = el.getAttribute('data-confirm') || 'Sigur dorești această acțiune?';
            if (!confirm(msg)) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    });

    // === Auto-hide flash messages după 5 secunde ===
    document.querySelectorAll('.flash').forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity 0.5s';
            el.style.opacity = '0';
            setTimeout(function () { el.style.display = 'none'; }, 500);
        }, 5000);
    });

    // === Validare basic CNP la blur ===
    document.querySelectorAll('input[data-validate="cnp"]').forEach(function (input) {
        input.addEventListener('blur', function () {
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
    document.querySelectorAll('input[type="email"]').forEach(function (input) {
        input.addEventListener('blur', function () {
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
    document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = document.querySelector(btn.getAttribute('data-toggle-password'));
            if (target) {
                target.type = target.type === 'password' ? 'text' : 'password';
            }
        });
    });

});

/**
 * Autocomplete din CNP: extrage sex, data nașterii, vârstă
 * CNP românesc: S AA LL ZZ JJ NNN C
 * S = sex (1,5=M, 2,6=F), AA=an, LL=luna, ZZ=zi, JJ=județ, NNN=nr, C=control
 */
function parseCNP(cnp) {
    if (!cnp || cnp.length !== 13 || !/^\d{13}$/.test(cnp)) return null;

    var s = parseInt(cnp[0]);
    var year = parseInt(cnp.substring(1, 3));
    var month = parseInt(cnp.substring(3, 5));
    var day = parseInt(cnp.substring(5, 7));

    // Determinare secol din prima cifră
    if (s === 1 || s === 2) year += 1900;
    else if (s === 3 || s === 4) year += 1800;
    else if (s === 5 || s === 6) year += 2000;
    else if (s === 7 || s === 8) year += 2000; // rezidenți
    else return null;

    // Sex
    var sex = (s % 2 === 1) ? 'Masculin' : 'Feminin';

    // Data nașterii
    var dataNasterii = year + '-' + String(month).padStart(2, '0') + '-' + String(day).padStart(2, '0');

    // Vârstă
    var today = new Date();
    var birthDate = new Date(year, month - 1, day);
    var age = today.getFullYear() - birthDate.getFullYear();
    var m = today.getMonth() - birthDate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) age--;

    return { sex: sex, dataNasterii: dataNasterii, varsta: age };
}

// Auto-attach pe câmpurile CNP din formulare
document.addEventListener('DOMContentLoaded', function () {
    var cnpInput = document.querySelector('input[name="cnp"]');
    if (!cnpInput) return;

    cnpInput.addEventListener('input', function () {
        var result = parseCNP(this.value);
        if (!result) return;

        var varstaField = document.querySelector('input[name="varsta"]');
        var sexField = document.querySelector('select[name="sex"], input[name="sex"]');
        var dataField = document.querySelector('input[name="data_nasterii"]');

        if (varstaField) varstaField.value = result.varsta;
        if (sexField) {
            if (sexField.tagName === 'SELECT') {
                sexField.value = result.sex;
            } else {
                sexField.value = result.sex;
            }
        }
        if (dataField) dataField.value = result.dataNasterii;
    });
});