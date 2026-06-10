<?php
   
    error_reporting(E_ALL);
    ini_set('display_errors', 0);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $formData = $_POST;

    $serverUrl = 'https://www.lbp-eservices.com/linkbiz/rs/postpayment';

    // Initialize cURL session
    $curl = curl_init();

    // Set cURL options
    curl_setopt($curl, CURLOPT_URL, $serverUrl);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($formData));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    // ==========================================
    // ADDED: Spoof Browser Headers to Bypass Firewall
    // ==========================================
    // Fake the User-Agent to look like Google Chrome on Windows
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
    
    // Add realistic browser HTTP headers
    $headers = array(
        "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8",
        "Accept-Language: en-US,en;q=0.9",
        "Connection: keep-alive",
        "Upgrade-Insecure-Requests: 1"
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    // Add a Referer to pretend we came from their domain
    curl_setopt($curl, CURLOPT_REFERER, 'https://www.lbp-eservices.com/');

    // Follow redirects if the server tries to bounce the connection
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

    // Enable cookies in memory (important for firewall session checks)
    curl_setopt($curl, CURLOPT_COOKIEJAR, 'php://memory');
    curl_setopt($curl, CURLOPT_COOKIEFILE, 'php://memory');

    // ==========================================
    // Bypass SSL verification for testing
    // ==========================================
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

    // Execute cURL session
    $response = curl_exec($curl);

    // Check for errors
    if ($response === false) {
        echo 'Error: ' . curl_error($curl);
    } else {
        // Output response
        $response = str_replace('00|', '', $response);
        $cleanResponse = trim($response); // Clean up any hidden whitespace

        // Check if the response is just a URL
        if (filter_var($cleanResponse, FILTER_VALIDATE_URL)) {
            echo 'O-LDPMS Redirecting...' ;
            echo "<script>window.location.href = '$cleanResponse';</script>";
        } else {
            // Echo the HTML directly so you can see what the error is on your screen.
            echo $cleanResponse;
        }

        exit; 
    }

    // Close cURL session
    curl_close($curl);
}
?>