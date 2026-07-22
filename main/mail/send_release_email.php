<?php

if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    http_response_code(403);
    exit('Forbidden');
}

function sendReleaseEmail(string $recipientEmail, string $recipientName): void
{
    $emailUrl = 'https://o-ldpms.denr.gov.ph/sendemail/send.php';

    $emailBody = '
        <p>Good day ' . htmlspecialchars($recipientName, ENT_QUOTES, 'UTF-8') . ',</p>
        <p>Your Certificate of Registration for Lumber Dealer has been approved and released.</p>
        <p>Please log in to your client dashboard to complete the remaining CSS requirements.</p>
        <p>Thank you.</p>
    ';

    $queryParams = http_build_query([
        'send' => 1,
        'email' => $recipientEmail,
        'Subject' => 'Certificate of Registration Released',
        'message' => $emailBody,
        'yourname' => 'O-LDPMS'
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $emailUrl . '?' . $queryParams);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error || $response === false) {
        error_log('Release email failed for ' . $recipientEmail . ': cURL error: ' . $error);
        throw new RuntimeException('Email sending failed for ' . $recipientEmail . ': cURL error: ' . $error);
    }
}
