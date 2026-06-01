<?php
header('Content-Type: application/json; charset=utf-8');

$start = isset($_GET['start']) ? $_GET['start'] : null;
$end   = isset($_GET['end']) ? $_GET['end'] : null;

$host = 'localhost';
$db   = 'meteo';
$user = 'makimo';
$pass = 'anto123';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

if (!$start || !$end) {
    echo json_encode([
        'success' => false,
        'message' => 'Paramètres start et end obligatoires.'
    ]);
    exit;
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
    echo json_encode([
        'success' => false,
        'message' => 'Format de date invalide.'
    ]);
    exit;
}

try {
    

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $sql = "SELECT date_mesure, temperature, humidite
            FROM donnees_capteurs
            WHERE date_mesure BETWEEN :start AND :end
            ORDER BY date_mesure ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':start' => $start . ' 00:00:00',
        ':end'   => $end . ' 23:59:59',
    ]);

    $rows = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'points'  => $rows,
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur base de données',
        'error'   => $e->getMessage(),
    ]);
}
