<?php

require __DIR__ . "/vendor/autoload.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);

    $dotenv->load();

    $database = new Database($_ENV["MARIADB_HOST"], $_ENV["MARIADB_DATABASE"], $_ENV["MARIADB_USER"], $_ENV["MARIADB_PASSWORD"]);

    $conn = $database->$getConnection();

    $sql = "INSERT INTO user (name, username, password_hash, api_key) VALUES (:name, :username, :password_hash, :api_key)";

    $stmt = $conn->prepare($sql);

    $password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $api_key = bin2hex(random_bytes(16));

    $stmt->bindValue(":name", $_POST["name"], PDO::PARAM_STR);
    $stmt->bindValue(":username", $_POST["username"], PDO::PARAM_STR);
    $stmt->bindValue(":password_hash", $_POST["password_hash"], PDO::PARAM_STR);
    $stmt->bindValue(":api_key", $_POST["api_key"], PDO::PARAM_STR);

    $stmt->execute();

    echo "Thank you for registering. Your API key is ", $api_key;
    exit;

}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css"
    >
</head>
<body>

    <h1>Register</h1>

    <main class="container">

        <form method="POST">

            <label for="name">
                Name:
                <input type="text" name="name" id="name" />
            </label>

            <label for="username">
                Username:
                <input type="text" name="username" id="username" />
            </label>

            <label for="password">
                Password:
                <input type="password" name="password" id="password" />
            </label>

            <button>Register</button>

        </form>
    </main>

</body>







</html>