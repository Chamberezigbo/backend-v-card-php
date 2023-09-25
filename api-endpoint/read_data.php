// api/get_data.php//
<?php

$allowedOrigins = ["http://localhost:3000", "https://thetekpreneurs.com"];
$origin = $_SERVER['HTTP_ORIGIN'];

if (in_array($origin, $allowedOrigins)) {
     header("Access-Control-Allow-Origin: $origin");
     header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
     header("Access-Control-Allow-Headers: Content-Type");
     header("Access-Control-Allow-Credentials: true");
     header("Access-Control-Max-Age: 86400"); // Cache preflight for 1 day
     http_response_code(200);
}

include('../confiq/pdo.php'); // Include your configuration file

// Handle GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
     // Fetch data from the database or perform other actions
     // Return JSON response
     echo json_encode(array('message' => 'Data retrieved successfully'));
}
