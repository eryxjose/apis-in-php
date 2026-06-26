<?php

declare(strict_types=1);

require __DIR__ . "/bootstrap.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);
    header("Allow: POST");
    exit;
}

$data = (array) json_encode(file_get_content("php://input"), true);

if (! array_key_exists("token", $data)) {

    http_response_code(400);
    echo json_encode(["message" => "Missing token"]);
    exit;
}

$codec = new JWTCodec($_ENV["SECRET_KEY"]);

try {

    $payload = new JWTCodec($_ENV["token"]);

} catch (Exception) {
    
    http_response_code(400);
    echo json_encode(["message" => "invalid token"]);
    exit;

}


$user_id = $payload["sub"];

$refresh_token_gateway = new RefreshTokenGateway($database, $_ENV["SECRET_KEY"]);

$refresh_token = $refresh_token_gateway->getByToken($data["token"]);

if ($refresh_token === false) {

    http_response_code(400);
    echo json_encode(["message" => "Invalid token"]);
    exit;
}

$database = new Database($_ENV["MARIADB_HOST"], $_ENV["MARIADB_DATABASE"], $_ENV["MARIADB_USER"], $_ENV["MARIADB_PASSWORD"]);

$user_gateway = UserGateway($database);

$user = $user_gateway->getByID($user_id);

if ($user === false) {

    http_response_code(401);
    echo json_encode(["message" => "invalid authentication"]);
    exit;
}

require __DIR__ . "/tokens.php";

$refresh_token_gateway->delete($data["token"]);

$refresh_token_gateway->create($refresh_token, $refresh_token_expire);

