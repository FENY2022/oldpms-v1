<?php

if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    http_response_code(403);
    exit('Forbidden');
}

require_once __DIR__ . '/../../processphp/config.php';
require_once __DIR__ . '/../../sendemail/phpmailer/src/Exception.php';
require_once __DIR__ . '/../../sendemail/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../../sendemail/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

function sendReleaseEmail(string $recipientEmail, string $recipientName): void
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;

        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($recipientEmail, $recipientName);
        $mail->isHTML(true);
        $mail->Subject = 'Certificate of Registration Released';
        $mail->Body = '
            <p>Good day ' . htmlspecialchars($recipientName, ENT_QUOTES, 'UTF-8') . ',</p>
            <p>Your Certificate of Registration for Lumber Dealer has been approved and released.</p>
            <p>Please log in to your client dashboard to complete the remaining CSS requirements.</p>
            <p>Thank you.</p>
        ';
        $mail->AltBody = 'Good day ' . $recipientName . ",\n\nYour Certificate of Registration for Lumber Dealer has been approved and released. Please log in to your client dashboard to complete the remaining CSS requirements.\n\nThank you.";

        $mail->send();
    } catch (Exception $e) {
        $mailError = isset($mail) ? $mail->ErrorInfo : $e->getMessage();
        error_log('Release email failed for ' . $recipientEmail . ': ' . $mailError);
        throw new RuntimeException('SMTP Error for ' . $recipientEmail . ': ' . $mailError, 0, $e);
    } catch (Throwable $e) {
        error_log('Release email setup failed for ' . $recipientEmail . ': ' . $e->getMessage());
        throw new RuntimeException('SMTP Error for ' . $recipientEmail . ': ' . $e->getMessage(), 0, $e);
    }
}
