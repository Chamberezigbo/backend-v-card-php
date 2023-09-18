<?php
// include my database
require_once "../methods/validate.php";
require_once "../config/config.php";
require_once "../config/pdo.php";
$db = new DatabaseClass();

require_once '../vendor/autoload.php'; // Include the JWT library
use Firebase\JWT\JWT;
use Firebase\JWT\Key;


// Handle CORS (Cross-Origin Resource Sharing)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');


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

          $socialLinkExist = validateDetails($decoded->data->user_id, 'social_links');
          if (!$socialLinkExist) {
               http_response_code(400); // Bad Request
               echo json_encode(array('message' => 'Social Link exist'));
               exit;
          }

          // Initialize variables for social media links
          $facebook = null;
          $linkedIn = null;
          $instagram = null;

          # Get data from the request body (assuming JSON Data)
          $data = json_decode(file_get_contents("php://input"));

          // Check if JSON data is provided and not empty
          if (!empty($data)) {
               // Retrieve and sanitize input data
               $facebook = isset($data->facebook) ? htmlspecialchars(strip_tags($data->facebook)) : "";
               $linkedIn = isset($data->linkedIn) ? htmlspecialchars(strip_tags($data->linkedIn)) : "";
               $instagram = isset($data->instagram) ? htmlspecialchars(strip_tags($data->instagram)) : "";
          }

          // Validate input data (you can add more validation as needed)
          if (empty($facebook) && empty($linkedIn) && empty($instagram)) {
               http_response_code(400); // Bad Request
               echo json_encode(array('message' => 'At least one social media link is required.'));
               exit;
          }

          // Save the social media links to the database
          $query = "
            INSERT INTO social_links (user_id, facebook, linkedIn, Instagram)
            VALUES (:user_id, :facebook, :linkedIn, :instagram)
        ";
          $dataArray = [
               'user_id' => $decoded->data->user_id,
               'facebook' => $facebook,
               'linkedIn' => $linkedIn,
               'instagram' => $instagram,
          ];

          $result = $db->Insert(
               $query,
               $dataArray
          );

          if ($result) {
               http_response_code(201); // Created
               echo json_encode(array('message' => 'Social media links saved successfully.'));
          } else {
               http_response_code(400);
               echo json_encode(array('message' => 'Social media links not saved.'));
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
