<?php

header('Content-Type: application/json; charset=utf-8');

$host = 'localhost';
$db   = 'meteo';
$user = 'makimo';
$pass = 'anto123';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Dernière ligne de la table
    $stmt = $pdo->query("
        SELECT date_mesure, temperature, humidite
        FROM donnees_capteurs
        ORDER BY date_mesure DESC
        LIMIT 1
    ");

    $row = $stmt->fetch();

    if ($row) {
        echo json_encode([
            'success'     => true,
            'date_mesure' => $row['date_mesure'],
            'temperature' => (float)$row['temperature'],
            'humidite'    => (float)$row['humidite'],
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Aucune donnée']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}