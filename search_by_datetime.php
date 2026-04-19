<?php
header('Content-Type: application/json; charset=utf-8');

$host = 'localhost';
$db   = 'meteo';
$user = 'makimo';
$pass = 'anto123';
$charset = 'utf8mb4';

if (!isset($_GET['datetime']) || empty($_GET['datetime'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Date/heure manquante'
    ]);
    exit;
}

$input = $_GET['datetime'];

$dateObj = DateTime::createFromFormat('Y-m-d\TH:i', $input);

if (!$dateObj) {
    echo json_encode([
        'success' => false,
        'message' => 'Format invalide',
        'received' => $input
    ]);
    exit;
}

$searchedDatetime = $dateObj->format('Y-m-d H:i:s');

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=$charset",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    $sql = "
        SELECT
            date_mesure,
            temperature,
            humidite,
            ABS(TIMESTAMPDIFF(SECOND, date_mesure, :searchedDatetime)) AS ecart_secondes
        FROM donnees_capteurs
        ORDER BY ecart_secondes ASC
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'searchedDatetime' => $searchedDatetime
    ]);

    $row = $stmt->fetch();

    if ($row) {
        echo json_encode([
            'success' => true,
            'requested_datetime' => $searchedDatetime,
            'date_mesure' => $row['date_mesure'],
            'temperature' => (float)$row['temperature'],
            'humidite' => (float)$row['humidite'],
            'ecart_secondes' => (int)$row['ecart_secondes']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Aucune donnée'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur SQL',
        'error' => $e->getMessage()
    ]);
}