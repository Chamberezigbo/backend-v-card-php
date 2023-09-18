<?php
// Load your configuration
require_once './confiq/config.php';

require_once './confiq/pdo.php';


// Handle CORS (Cross-Origin Resource Sharing)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Get the HTTP method and requested path
$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
$endpoint = array_shift($request);

// Define the API endpoints
$allowedEndpoints = ['data', 'record', 'update', 'delete']; // Add your endpoints here

// Check if the requested endpoint is valid
if (in_array($endpoint, $allowedEndpoints)) {
     // Include the appropriate endpoint files
     require_once "api/{$endpoint}.php";
} else {
     // Return an error response for invalid endpoints
     http_response_code(404);
     echo json_encode(array('message' => 'Invalid endpoint'));
}
