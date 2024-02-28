<?php

// include my database
require_once "../config/config.php";
require_once "../config/pdo.php";
cors();
$db = new DatabaseClass();

require_once '../vendor/autoload.php'; // Include the JWT library
use Firebase\JWT\JWT;


// check if its post request//
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
     # Get data from the request body (assuming JSON Data)
     $data = json_decode(file_get_contents("php://input"));

     // Validate user input
     if (
          empty($data->email) ||
          empty($data->password) ||
          empty($data->fullName) ||
          empty($data->phone) ||
          empty($data->date_of_birth)
     ) {
          http_response_code(400); // Bad request
          echo json_encode(array('message' => 'Invalid input data'));
     } else {
          // Sanitize and hash the password
          $full_name = htmlspecialchars(strip_tags($data->fullName));
          $email = filter_var($data->email, FILTER_VALIDATE_EMAIL) ? $data->email : null;
          $phone = preg_match('/^\+?[0-9]{10,}$/', $data->phone) ? $data->phone : null;
          $date_of_birth = strtotime($data->date_of_birth) ? date('Y-m-d', strtotime($data->date_of_birth)) : null;

          if (!$email || !$phone || !$date_of_birth || !$full_name) {
               http_response_code(400); // Bad request
               echo json_encode(array('message' => 'Wrong input inserted'));
          } else {
               #check if email exist
               $existingUser = $db->SelectOne("SELECT * FROM users WHERE email = :email", ['email' => $email]);

               if ($existingUser) {
                    http_response_code(409); // Conflict - email already exists
                    echo json_encode(array('message' => 'Email already registered'));
               } else {
                    #hash password before saving
                    $password_hash = password_hash($data->password, PASSWORD_BCRYPT);
                    $user_id = md5(time() . $email);

                    #insert user into database
                    try {
                         $quarry = " 
                              INSERT INTO users (user_id,full_name,email,password,phone,date_of_birth)
                              VALUES(:user_id,:full_name,:email,:password,:phone,:date_of_birth)
                         ";
                         $dataArray = [
                              'user_id' => $user_id,
                              'full_name' => $full_name,
                              'email' => $email,
                              'password' => $password_hash,
                              'phone' => $phone,
                              'date_of_birth' => $date_of_birth,
                         ];

                         $result = $db->Insert($quarry, $dataArray);
                         if ($result) {

                              $tokenPayload['aud'] = $email;
                              $tokenPayload['data'] = array(
                                   'user_id' => $user_id,
                                   'email' => $email,
                              );
                              $token = JWT::encode($tokenPayload, $key, 'HS256');

                              http_response_code(201); // Created
                              echo json_encode(array('message' => 'User registered successfully', 'token' => $token));
                         } else {
                              http_response_code(400); // Created
                              echo json_encode(array('message' => 'User not registered'));
                         }
                    } catch (PDOException $e) {
                         //throw error
                         http_response_code(500); // Internal Server Error
                         echo json_encode(array('message' => 'Error: ' . $e->getMessage()));
                    }
               }
          };
     }
} else {
     http_response_code(405); // Method Not Allowed
     echo json_encode(array('message' => 'Invalid request method'));
}
