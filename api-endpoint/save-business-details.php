<?php
// include my database
require_once "../config/config.php";
require_once "../config/pdo.php";
require_once "../methods/validate.php";
cors();
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

          $socialLinkExist = validateDetails($decoded->data->user_id, 'business_details');
          if (!$socialLinkExist) {
               http_response_code(400); // Bad Request
               echo json_encode(array('message' => 'Business exist'));
               exit;
          }

          # Get data from the request body (assuming JSON Data)
          $data = json_decode(file_get_contents("php://input"));


          // Retrieve and sanitize input data
          $name = htmlspecialchars(strip_tags($_POST['name']));
          $description = htmlspecialchars(strip_tags($_POST['description']));
          $location = htmlspecialchars(strip_tags($_POST['location']));

          // Validate input data
          if (empty($name) || empty($description) || empty($location)) {
               http_response_code(400); // Bad Request
               echo json_encode(array('message' => 'All fields are required.'));
               exit;
          }
          // Check if an image file was uploaded
          if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
               http_response_code(400); // Bad Request
               echo json_encode(array('message' => 'Please select an image.'));
               exit;
          }

          // Define the upload directory (where you want to save the images)
          $uploadDirectory = '../uploads/'; // You may need to create this directory

          // Generate a unique filename for the uploaded image
          $originalFilename = basename($_FILES['logo']['name']);
          $extension = pathinfo(
               $originalFilename,
               PATHINFO_EXTENSION
          );
          $uniqueFilename = uniqid() . '.' . $extension;
          $targetPath = $uploadDirectory . $uniqueFilename;

          // Move the uploaded file to the target path
          if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetPath)) {
               // File was successfully moved
               // Now, $targetPath contains the path to the saved image
               $quarry = "
                    INSERT INTO business_details (user_id,name,description,location,logo)
                    VALUES(:user_id,:name,:description,:location,:logo)
               ";
               $dataArray = [
                    'user_id' => $decoded->data->user_id,
                    'name' => $name,
                    'description' => $description,
                    'location' => $location,
                    'logo' => $targetPath,
               ];
               $result = $db->Insert($quarry, $dataArray);
               if ($result) {
                    # code...
                    http_response_code(201); // Created
                    echo json_encode(array('message' => 'Business details saved successfully.'));
               } else {
                    http_response_code(400);
                    echo json_encode(array('message' => 'Business not registered'));
               }
          } else {
               // File upload failed
               http_response_code(500); // Internal Server Error
               echo json_encode(array('message' => 'Failed to upload the image.'));
               exit;
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
