<?php

$host = 'mariadb';
$dbname = 'appdb';
$user = 'appuser';
$password = 'change_me';

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $stmt = $pdo->query("SELECT id, name, price, created_at FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    http_response_code(500);
    echo "Erro ao conectar ao banco: " . htmlspecialchars($e->getMessage());
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>PHP + MariaDB Demo</title>
</head>
<body>
    <h1>Produtos</h1>

    <table border="1" cellpadding="8">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Preço</th>
                <th>Criado em</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= htmlspecialchars($product['id']) ?></td>
                    <td><?= htmlspecialchars($product['name']) ?></td>
                    <td>R$ <?= htmlspecialchars($product['price']) ?></td>
                    <td><?= htmlspecialchars($product['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>