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

// Include your configuration and database connection here
require_once "../config/config.php";
require_once "../config/pdo.php";
$db = new DatabaseClass();

require_once '../vendor/autoload.php'; // Include the JWT library
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
     // Get the JWT token from the Authorization header
     $authHeader = getallheaders();
     $token = isset($authHeader['Authorization']) ? str_replace('Bearer ', '', $authHeader['Authorization']) : null;

     if (!$token) {
          http_response_code(401); // Unauthorized
          echo json_encode(array('message' => 'Unauthorized'));
          exit;
     }

     try {
          // Verify the token
          $decoded = JWT::decode($token, new Key($key, 'HS256'));

          // Perform logout actions if needed (e.g., invalidate the token on the server-side)

          // Respond with a successful logout message
          http_response_code(200); // OK
          echo json_encode(array('message' => 'Logout successful.'));
     } catch (Exception $e) {
          http_response_code(401); // Unauthorized
          echo json_encode(array('message' => 'Error:' . $e->getMessage()));
     }
} else {
     http_response_code(405); // Method Not Allowed
     echo json_encode(array('message' => 'Invalid request method'));
}
