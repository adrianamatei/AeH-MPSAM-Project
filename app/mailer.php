<?php
/**
 * Configurare SMTP pentru trimitere email-uri
 * Folosește PHPMailer cu Gmail SMTP
 */

// Calculăm corect calea relativă ieșind din folderul 'app' pentru a intra în 'lib'
$phpmailerPath = dirname(__DIR__) . '/lib/PHPMailer/PHPMailer.php';

if (file_exists($phpmailerPath)) {
    require_once dirname(__DIR__) . '/lib/PHPMailer/Exception.php';
    require_once dirname(__DIR__) . '/lib/PHPMailer/PHPMailer.php';
    require_once dirname(__DIR__) . '/lib/PHPMailer/SMTP.php';
    define('PHPMAILER_AVAILABLE', true);
} else {
    define('PHPMAILER_AVAILABLE', false);
    error_log("PHPMailer NU a fost găsit la calea: " . $phpmailerPath);
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ═══ CONFIGURARE SMTP ═══
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'ionelaamatei2004@gmail.com');      
define('SMTP_PASS', 'vrrw fuqf hvxo uzjk'); // Asigură-te că folosești o parolă de aplicație validă și activă
define('SMTP_FROM_NAME', 'Vital Cares');
define('SMTP_FROM_EMAIL', 'ionelaamatei2004@gmail.com');    

/**
 * Creează o instanță PHPMailer configurată
 */
function createMailer() {
    $mail = new PHPMailer(true);
    
    // Configurare server SMTP
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';
    
    // Expeditor
    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    
    return $mail;
}

/**
 * Trimite email de bun venit pacientului cu credențiale
 */
function sendWelcomeEmail($toEmail, $prenume, $defaultPassword) {
    if (!PHPMAILER_AVAILABLE) {
        error_log("PHPMailer nu este instalat. Email netrimis către: {$toEmail}");
        return false;
    }
    
    try {
        $mail = createMailer();
        $mail->addAddress($toEmail);
        
        // Modificat: Link-ul trimite acum către login.php pentru ca pacientul să se poată autentifica în contul său
// Adăugăm action=logout și trimitem și email-ul pacientului ca parametru în link
        $loginLink = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' 
                . ($_SERVER['HTTP_HOST'] ?? 'localhost:8000') . '/login.php?action=logout&target_email=' . urlencode($toEmail);
                
        // Email HTML
        $mail->isHTML(true);
        $mail->Subject = 'Vital Cares - Contul tău a fost creat';
        
        $mail->Body = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
            <div style="background: linear-gradient(135deg, #0D7C5F, #10A37F); padding: 30px; border-radius: 12px 12px 0 0; text-align: center;">
                <h1 style="color: white; margin: 0; font-size: 24px;">♥ Vital Cares</h1>
                <p style="color: rgba(255,255,255,0.8); margin: 8px 0 0;">Sistem de monitorizare a sănătății</p>
            </div>
            
            <div style="background: #ffffff; padding: 30px; border: 1px solid #e0e0e0; border-top: none;">
                <h2 style="color: #333; margin-top: 0;">Bună ziua, ' . htmlspecialchars($prenume) . '!</h2>
                
                <p style="color: #555; line-height: 1.6;">
                    Medicul dumneavoastră v-a creat un cont în sistemul <strong>Vital Cares</strong> 
                    pentru monitorizarea stării de sănătate.
                </p>
                
                <div style="background: #f0faf7; border: 1px solid #0D7C5F; border-radius: 8px; padding: 20px; margin: 20px 0;">
                    <h3 style="color: #0D7C5F; margin-top: 0;">🔐 Date de autentificare</h3>
                    <table style="width: 100%;">
                        <tr>
                            <td style="padding: 5px 0; color: #666;"><strong>Email:</strong></td>
                            <td style="padding: 5px 0;">' . htmlspecialchars($toEmail) . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 5px 0; color: #666;"><strong>Parolă temporară:</strong></td>
                            <td style="padding: 5px 0; font-family: monospace; font-size: 16px; color: #0D7C5F;"><strong>' . htmlspecialchars($defaultPassword) . '</strong></td>
                        </tr>
                    </table>
                </div>
                
                <div style="background: #fff8e8; border-left: 4px solid #B8860B; padding: 15px; margin: 20px 0; border-radius: 0 8px 8px 0;">
                    <p style="margin: 0; color: #856404;">
                        ⚠ <strong>Important:</strong> Vă recomandăm să vă schimbați parola la prima autentificare 
                        pentru securitatea contului dumneavoastră.
                    </p>
                </div>
                
                <div style="text-align: center; margin: 30px 0;">
                    <a href="' . htmlspecialchars($loginLink) . '" 
                       style="background: #0D7C5F; color: white; padding: 14px 28px; border-radius: 8px; 
                              text-decoration: none; font-weight: bold; font-size: 16px; display: inline-block;">
                        🔑 Conectează-te și schimbă parola
                    </a>
                </div>
                
                <p style="color: #999; font-size: 12px; margin-top: 30px; border-top: 1px solid #eee; padding-top: 15px;">
                    Acest email a fost trimis automat de sistemul Vital Cares. 
                    Nu răspundeți la acest mesaj.
                </p>
            </div>
            
            <div style="background: #f5f5f5; padding: 15px; border-radius: 0 0 12px 12px; text-align: center;">
                <p style="margin: 0; color: #999; font-size: 11px;">
                    © ' . date('Y') . ' Vital Cares · Sistem purtabil de supraveghere a stării de sănătate
                </p>
            </div>
        </div>';
        
        // Versiune text simplu (fallback)
        $mail->AltBody = "Bună ziua, {$prenume}!\n\n"
            . "Medicul dumneavoastră v-a creat un cont în sistemul Vital Cares.\n\n"
            . "Date de autentificare:\n"
            . "Email: {$toEmail}\n"
            . "Parolă temporară: {$defaultPassword}\n\n"
            . "Autentificați-vă la: {$loginLink}\n\n"
            . "Cu stimă,\nEchipa Vital Cares";
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Email send failed to {$toEmail}: " . $e->getMessage());
        return false;
    }
}

/**
 * Trimite email generic (pentru alte notificări)
 */
function sendEmail($toEmail, $subject, $htmlBody, $textBody = '') {
    if (!PHPMAILER_AVAILABLE) return false;
    try {
        $mail = createMailer();
        $mail->addAddress($toEmail);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = $textBody ?: strip_tags($htmlBody);
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email send failed: " . $e->getMessage());
        return false;
    }
}