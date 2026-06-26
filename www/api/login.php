<?php

declare(strict_types=1);

require __DIR__ . "/bootstrap.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);
    header("Allow: POST");
    exit;
}

$data = (array) json_encode(file_get_content("php://input"), true);

if (! array_key_exists("username", $data) || ! array_key_exists("password", $data)) {

    http_response_code(400);
    echo json_encode(["message" => "Missing login credentials"]);
    exit;
}

$database = new Database($_ENV["MARIADB_HOST"], $_ENV["MARIADB_DATABASE"], $_ENV["MARIADB_USER"], $_ENV["MARIADB_PASSWORD"]);

$user_gateway = new UserGateway($database);

$user = $user_gateway->getByUsername($data["username"]);

if ($user === false) {

    http_response_code(401);
    echo json_encode(["message" => "Invalid authentication"]);
    exit;
}

if (! password_verify($data["password"], $user["password_hash"])) {

    http_response_code(401);
    echo json_encode(["message" => "Invalid authentication"]);
    exit;
}

$codec = new JWTCodec($_ENV["SECRET_KEY"]);

require __DIR__ . "/tokens.php";

$refresh_token_gateway = new RefreshTokenGateway($database, $_ENV["SECRET_KEY"]);

$refresh_token_gateway->create($refresh_token, $refresh_token_expire);



