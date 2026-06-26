<?php

declare(strict_types=1);

require __DIR__ . "/bootstrap.php";

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

$parts = explode("/", $path);

$resource = $parts[2];

$id = $parts[3] ?? null;

if ($resource != "tasks") {
    http_response_code(404);
    exit;
}

$database = new Database($_ENV["MARIADB_HOST"], $_ENV["MARIADB_DATABASE"], $_ENV["MARIADB_USER"], $_ENV["MARIADB_PASSWORD"]);

$user_gateway = new UserGateway($database);


// $headers = apache_request_headers();

// echo $headers["Authorization"];

$auth = new Auth($user_gateway);

// if (! $auth->authenticateAPIKey()) {
//     exit;
// }

if (! $auth->authenticateAccessToken()) {
    exit;
}


$user_id = $auth->getUserID();




// $database->getConnection();

$task_gateway = new TaskGateway($database);

$controller = new TaskController($task_gateway, $user_id);

$controller->processRequest($_SERVER["REQUEST_METHOD"], $id);
