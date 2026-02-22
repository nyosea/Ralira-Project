<?php
/**
 * Google OAuth Configuration
 */

define('GOOGLE_CLIENT_ID', '979822998918-cec7b3h4khv0aq9pa16ltp228ojp198n.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URI', 'http://localhost/ralira_project/pages/auth/google-callback.php');

function getGoogleClient() {
    require_once __DIR__ . '/../vendor/autoload.php';
    
    $client = new Google_Client();
    $client->setClientId(GOOGLE_CLIENT_ID);
    $client->setClientSecret(GOOGLE_CLIENT_SECRET);
    $client->setRedirectUri(GOOGLE_REDIRECT_URI);
    $client->addScope("email");
    $client->addScope("profile");
    
    // FIX: Disable SSL verification (untuk local development)
    $guzzleClient = new \GuzzleHttp\Client([
        'verify' => false
    ]);
    $client->setHttpClient($guzzleClient);
    
    return $client;
}