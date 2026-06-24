<?php

declare(strict_types=1);

require dirname(__DIR__) . "/vendor/autoload.php";

set_exception_handler("ErrorHandler::handleException");

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

$parts = explode("/", $path);

$resource = $parts[2];

$id = $parts[3] ?? null;

if ($resource != "tasks") {
    http_response_code(404);
    exit;
}

handler("Content-Type: application/json; charset=UTF-8");

$database = new Database("localhost", "apidb", "appuser", "change_me");

$database->getConnection();

$controller = new TaskController;

$controller->processRequest($_SERVER["REQUEST_METHOD"], $id);
