<?php


$payload = [
    "sub" => $user["id"],
    "name" => $user["name"],
    "exp" => time() + 300
];

// $access_token = base64_encode(json_encode($payload));

$access_token = $codec->encode($payload);

$refresh_token = $codec->encode([
    "sub" => $user["id"],
    "exp" => time() + 432000
]);

echo json_encode([
    "access_token" => $access_token
]);


$refresh_token_gateway->create($refresh_token, $refresh_token_expire);

