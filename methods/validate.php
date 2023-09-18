<?php
require_once "../config/pdo.php";

function validateUser($email, $password)
{
     global $db; // Assuming you have a PDO instance named $db

     // SQL query to fetch user information based on email
     $user = $db->SelectOne("SELECT user_id, email, password FROM users WHERE email = :email ", ['email' => $email]);
     if ($user && password_verify($password, $user['password'])) {
          $businessQuery = $db->SelectOne("SELECT user_id FROM business_details WHERE user_id = :user_id", ['user_id' => $user['user_id']]);
          if ($businessQuery) {
               $socialQuery = $db->SelectOne("SELECT user_id FROM social_links WHERE user_id = :user_id", ['user_id' => $user['user_id']]);
               if ($socialQuery) {
                    return $user; // Return user data
               } else {
                    return ['error' => "Missing Social Links", 'user' => $user];
               }
          } else {
               return ['error' => "Missing Business Details", 'user' => $user];
          }
     }

     return ['error' => 'Invalid email or password.']; // Invalid email or password
}

function validateDetails($userId, $databaseCredentials)
{
     global $db; // Assuming you have a PDO instance named $db

     $userCredentialExist = $db->SelectOne("SELECT user_id  FROM $databaseCredentials WHERE user_id = :user_id", ['user_id' => $userId]);
     if ($userCredentialExist) {
          return false;
     }
     return true;
}
