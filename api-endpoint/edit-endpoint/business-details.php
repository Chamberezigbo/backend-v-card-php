<?php
// include my database
require_once "../../config/config.php";
require_once "../../config/pdo.php";
require_once "../../methods/validate.php";
require_once "../../methods/getExistingLogoPath.php";
cors();
$db = new DatabaseClass();

require_once '../../vendor/autoload.php'; // Include the JWT library
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

        // Get data from the request body (assuming JSON Data)
        $data = json_decode(file_get_contents("php://input"));

        // Retrieve and sanitize input data
        $name = htmlspecialchars(strip_tags($data->name));
        $description = htmlspecialchars(strip_tags($data->description));
        $location = htmlspecialchars(strip_tags($data->location));

        // Validate input data
        if (empty($name) || empty($description) || empty($location)) {
            http_response_code(400); // Bad Request
            echo json_encode(array('message' => 'All fields are required.'));
            exit;
        }

        // Check if an image file was uploaded
        if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
              // No new logo uploaded, keep the existing logo path
              $existingLogoPath = $db->getExistingLogoPath($decoded->data->user_id);
              $logoPath = $existingLogoPath; // Assuming you have a field in your database for the logo path
        } else {
            // Handle logo upload

            // Define the upload directory (where you want to save the images)
            $uploadDirectory = '../../uploads/'; // You may need to create this directory

            // Get the existing logo path
            $existingLogoPath = $db->getExistingLogoPath($decoded->data->user_id);

            // Delete the previous file if it exists
            if ($existingLogoPath) {
                $existingLogoFullPath = realpath($existingLogoPath);
                if ($existingLogoFullPath && file_exists($existingLogoFullPath)) {
                    unlink($existingLogoFullPath);
                }
            }

            // Generate a unique filename for the uploaded image
            $originalFilename = basename($_FILES['logo']['name']);
            $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
            $uniqueFilename = uniqid() . '.' . $extension;
            $targetPath = $uploadDirectory . $uniqueFilename;

            // Move the uploaded file to the target path
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetPath)) {
                // File was successfully moved
                // Now, $targetPath contains the path to the saved image
                $logoPath = $targetPath;
            } else {
                // File upload failed
                http_response_code(500); // Internal Server Error
                echo json_encode(array('message' => 'Failed to upload the image.'));
                exit;
            }
        }

        // Update the existing record with new data
        $query = "
            UPDATE business_details
            SET name = :name, description = :description, location = :location, logo = :logo
            WHERE user_id = :user_id
        ";
        $params = [
            'user_id' => $decoded->data->user_id,
            'name' => $name,
            'description' => $description,
            'location' => $location,
            'logo' => $logoPath, // Use the new logo path or the existing one if no new logo is uploaded
        ];
        $result = $db->Update($query, $params);

        if ($result) {
            http_response_code(200); // OK
            echo json_encode(array('message' => 'Business details updated successfully.'));
        } else {
            http_response_code(500);
            echo json_encode(array('message' => 'Failed to update business details.'));
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
