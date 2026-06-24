<?php

$api_key = "YOUR_API_KEY";

$data = [
    "name" => "Bob",
    "email" => "bob@example.com"
];

require __DIR__ . "/vendor/autoload.php";

$stripe = new \Stripe\StripeClient($api_key);

$customer = $stripe->customers->create($data);

echo $customer;

/*
$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => 'https://api.stripe.com/v1/customers',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERPWD => $api_key,
    CURLOPT_POSTFIELDS => http_build_query($data)
]);

$response = curl_exec($ch);

curl_close($ch);

echo $response;
*/





