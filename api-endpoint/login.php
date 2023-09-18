<?php
// include my database
require_once "../config/config.php";
require_once "../config/pdo.php";
require_once "../methods/validate.php";
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
     // Get data from the request body (assuming JSON Data)
     $data = json_decode(file_get_contents("php://input"));

     // Retrieve and sanitize input data
     $email = htmlspecialchars(strip_tags($data->email));
     $password = htmlspecialchars(strip_tags($data->password));

     $user = validateUser($email, $password); // Implement this function

     // Call the validateUser function
     $user = validateUser($email, $password);

     // Check if the returned value contains an "error" key
     if (isset($user['error']) && $user['error'] == "Invalid email or password.") {
          // Handle the error
          $errorMessage = $user['error'];

          // You can return the error message as a JSON response or handle it as needed
          http_response_code(401); // Unauthorized
          echo json_encode(array('message' => $errorMessage));
          exit;
     } else {
          $errorMessage = $user['error'];

          $tokenPayload['aud'] = $email;
          $tokenPayload['data'] = array(
               'user_id' => $user['user']['user_id'],
               'email' => $email,
          );
          $token = JWT::encode($tokenPayload, $key, 'HS256');

          // You can return the error message as a JSON response or handle it as needed
          http_response_code(201); // Unauthorized
          echo json_encode(array('message' => $errorMessage, 'token' => $token));
          exit;
     }


     $tokenPayload['aud'] = $email;
     $tokenPayload['data'] = array(
          'user_id' => $user['user_id'],
          'email' => $email,
     );

     $token = JWT::encode($tokenPayload, $key, 'HS256');
     // Respond with the JWT token
     http_response_code(200); // OK
     echo json_encode(array('message' => 'Login successful', 'token' => $token));
} else {
     http_response_code(405); // Method Not Allowed
     echo json_encode(array('message' => 'Invalid request method'));
}
