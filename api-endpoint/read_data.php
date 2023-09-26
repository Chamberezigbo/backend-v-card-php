// api/get_data.php//
<?php

include('../confiq/pdo.php'); // Include your configuration file
cors();

// Handle GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
     // Fetch data from the database or perform other actions
     // Return JSON response
     echo json_encode(array('message' => 'Data retrieved successfully'));
}
