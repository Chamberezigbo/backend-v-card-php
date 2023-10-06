<?php
require_once "../config/config.php";
require_once "../config/pdo.php";
cors();
$db = new DatabaseClass();

require_once '../vendor/autoload.php'; // Include the JWT library
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
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

          $userId = $decoded->data->user_id;
          $businessQuery = $db->SelectOne(
               "SELECT users.*, business_details.*, social_links.*
          FROM users
          INNER JOIN business_details ON users.user_id = business_details.user_id
          INNER JOIN social_links ON users.user_id = social_links.user_id
          WHERE users.user_id = :user_id",
               ['user_id' => $userId]
          );

          if ($businessQuery) {
               http_response_code(200); // Created
               echo json_encode(array('user' => $businessQuery));
          } else {
               http_response_code(404); // Created
               echo json_encode(array('message' => "User not found"));
          }
     } catch (Exception $e) {
          // Other exceptions
          echo "Error: " . $e->getMessage() . "<br>";
          http_response_code(401); // Unauthorized
          echo json_encode(array('message' => 'Error:' . $e->getMessage()));
     }
} else {
     http_response_code(405); // Method Not Allowed
     echo json_encode(array('message' => 'Invalid request method'));
}

