<?php
// JWT secret key (keep this secret)
$key = 'Nmesoma@5050';
$expirationTime = time() + 3600; // Token expires in 1 hour

// Token payload
$tokenPayload = array(
     "iss" => "business_card",  // Issuer
     "aud" => "", // Audience
     "iat" => time(),           // Issued At
     "exp" => $expirationTime,             // Expiration time (1 hour)
     "data" =>  "" // User data
);

// ...
