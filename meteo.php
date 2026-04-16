<?php
header('Content-Type: application/json; charset=utf-8');

$host = 'localhost';
$db   = 'meteo';              // ou le nom réel
$user = 'adminbddnayrod360';
$pass = 'TON_MDP_BITWARDEN';
$charset = 'utf8mb4';

// === AFFICHAGE DES VARIABLES ===
echo "<h3>📋 Contenu des variables :</h3>";
echo "Server: <strong>" . htmlspecialchars($servername) . "</strong><br>";
echo "Username: <strong>" . htmlspecialchars($username) . "</strong><br>";
echo "Database: <strong>" . htmlspecialchars($database) . "</strong><br><br>";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

echo "✅ Connexion réussie !<br><br>";

// === DÉFINITION DES TABLES (MANQUANT) ===
$tables = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        role VARCHAR(20) DEFAULT 'user',
        email VARCHAR(100) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS donnees_capteurs (
        id_donnees_capteurs INT AUTO_INCREMENT PRIMARY KEY,
        id_immeuble INT NOT NULL,
        date_mesure DATETIME DEFAULT CURRENT_TIMESTAMP,
        temperature FLOAT,
        pression FLOAT,
        humidite FLOAT
    )"
];

echo "<strong>📂 Tables définies :</strong><br>";
foreach ($tables as $i => $sql) {
    echo "Table " . ($i+1) . " : " . substr($sql, 0, 80) . "...<br>";
}

foreach ($tables as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "✅ Table créée avec succès.<br>";
    } else {
        echo "❌ Erreur table : " . $conn->error . "<br>";
    }
}

// === AFFICHAGE UTILISATEURS ===
echo "<br><h3>👥 Utilisateurs :</h3>";
$utilisateurs = [
    ['username' => 'mathys', 'email' => 'mathys@admin.fr', 'motdepasse' => 'admin123', 'role' => 'admin'],
    ['username' => 'collegue1', 'email' => 'collegue1@test.fr', 'motdepasse' => 'collegue123', 'role' => 'user'],
    ['username' => 'collegue2', 'email' => 'collegue2@test.fr', 'motdepasse' => 'collegue456', 'role' => 'user']
];

print_r($utilisateurs); // Debug complet

$stmt_user = $conn->prepare("INSERT INTO users (username, role, email, password_hash) VALUES (?, ?, ?, ?)");

foreach ($utilisateurs as $user) {
    echo "<br>➤ Traitement : " . $user['username'];
    $hash = password_hash($user['motdepasse'], PASSWORD_DEFAULT);
    echo " (hash: " . substr($hash, 0, 20) . "... )";
    
    $stmt_user->bind_param("ssss", $user['username'], $user['role'], $user['email'], $hash);

    if ($stmt_user->execute()) {
        echo " → ✅ créé<br>";
    } else {
        $error_code = $conn->errno;
        if ($error_code == 1062) {
            echo " → ⚠️ existe déjà<br>";
        } else {
            echo " → ✗ Erreur : " . $stmt_user->error . "<br>";
        }
    }
}

$stmt_user->close();

// === GESTION CAPTEURS ===
echo "<br><h3>🌡️ Capteurs :</h3>";
if (
    isset($_GET['id_immeuble']) &&
    isset($_GET['temperature']) &&
    isset($_GET['humidite']) &&
    isset($_GET['pression'])
) {
    echo "Paramètres GET reçus !<br>";
    $id_immeuble = intval($_GET['id_immeuble']);
    $temperature = floatval($_GET['temperature']);
    $humidite = floatval($_GET['humidite']);
    $pression = floatval($_GET['pression']);
    
    echo "id_immeuble: $id_immeuble, T: $temperature, H: $humidite, P: $pression<br>";
    
    // Insertion...
} else {
    echo "Aucun paramètre capteur (normal si test manuel)<br>";
}

$conn->close();

echo "<br><strong>🎉 Identifiants :</strong><br>";
echo "- Admin : mathys@admin.fr / admin123<br>";
echo "- User1 : collegue1@test.fr / collegue123<br>";
echo "- User2 : collegue2@test.fr / collegue456<br>";
?>
