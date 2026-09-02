<?php

namespace Project\Core;

require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/**
 * Envoi d'emails via PHPMailer (SMTP), configuré depuis les constantes MAIL_* du .env.
 */
class Mailer
{
    private static string $lastError = '';

    public static function getLastError(): string
    {
        return self::$lastError;
    }

    /**
     * Envoie un email HTML. Retourne true si l'envoi a réussi, false sinon.
     */
    public static function send(string $to, string $toName, string $subject, string $htmlBody): bool
    {
        self::$lastError = '';
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->Port       = (int) MAIL_PORT;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USERNAME;
            $mail->Password   = MAIL_PASSWORD;
            $mail->SMTPSecure = MAIL_ENCRYPTION;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
            $mail->addAddress($to, $toName);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;

            $mail->send();
            return true;
        } catch (PHPMailerException $e) {
            self::$lastError = $mail->ErrorInfo ?: $e->getMessage();
            error_log('[Mailer] Envoi échoué : ' . self::$lastError);
            return false;
        }
    }
}
