<?php
require_once "../config/config.php";
include('../config/pdo.php'); // Include your configuration file
$db = new DatabaseClass();
cors();

// Handle GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
     $businessId = $_GET['business'];

     $businessQuery = $db->SelectOne(
          "SELECT users.*, business_details.*, social_links.*
          FROM users
          INNER JOIN business_details ON users.user_id = business_details.user_id
          INNER JOIN social_links ON users.user_id = social_links.user_id
          WHERE users.user_id = :user_id",
          ['user_id' => $businessId]
     );

     if ($businessQuery) {
          http_response_code(200); // Created
          echo json_encode(array('user' => $businessQuery));
     } else {
          http_response_code(404); // Created
          echo json_encode(array('message' => "User not found"));
     }
} else {
     http_response_code(405); // Method Not Allowed
     echo json_encode(array('message' => 'Invalid request method'));
}
