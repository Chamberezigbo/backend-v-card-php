<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

function cors()
{
    $allowedOrigins = ["http://localhost:3000", "https://thetekpreneurs.com"];

    // Check if 'HTTP_ORIGIN' is set in the $_SERVER array
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : null;

    // Allow from any origin
    if ($origin && in_array($origin, $allowedOrigins)) {
        // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
        // you want to allow, and if so:
        header("Access-Control-Allow-Origin: $origin");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400'); // cache preflight requests for 1 day
        // Don't cache response
        header("Cache-Control: no-cache, must-revalidate"); //HTTP 1.1
        header("Pragma: no-cache"); //HTTP 1.0
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
    }

    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            // may also be using PUT, PATCH, HEAD etc
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

        exit(0);
    }
}


// JWT secret key (keep this secret)
$key = 'Nmesoma@5050';
$expirationTime = time() + 3600; // Token expires in 1 hour

// Token payload
$tokenPayload = array(
     "iss" => "business_card",  // Issuer
     "aud" => "", // Audience
     "iat" => time(),           // Issued At
     "exp" => $expirationTime,             // Expiration time (1 hour)
     "data" =>  "" // User data
);

// ...
