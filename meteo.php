<?php
$servername = "10.2.2.38";
$username = "mathys";
$password = "1234";
$database = "meteo";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

echo "Connexion réussie !<br>";

// Création des tables si elles n'existent pas (avec role ajouté)
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
        humidite FLOAT,
        FOREIGN KEY (id_immeuble) REFERENCES immeubles(id_immeuble)
    )"
];

foreach ($tables as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Table créée avec succès.<br>";
    } else {
        echo "Erreur table : " . $conn->error . "<br>";
    }
}

// 1. CRÉATION AUTOMATIQUE DES UTILISATEURS (admin + collègues)
$utilisateurs = [
    ['username' => 'mathys', 'email' => 'mathys@admin.fr', 'motdepasse' => 'admin123', 'role' => 'admin'],
    ['username' => 'collegue1', 'email' => 'collegue1@test.fr', 'motdepasse' => 'collegue123', 'role' => 'user'],
    ['username' => 'collegue2', 'email' => 'collegue2@test.fr', 'motdepasse' => 'collegue456', 'role' => 'user']
];

$stmt_user = $conn->prepare("INSERT INTO users (username, role, email, password_hash) VALUES (?, ?, ?, ?)");

foreach ($utilisateurs as $user) {
    $hash = password_hash($user['motdepasse'], PASSWORD_DEFAULT);
    $stmt_user->bind_param("ssss", $user['username'], $user['role'], $user['email'], $hash);

    if ($stmt_user->execute()) {
        echo "✓ {$user['username']} ({$user['role']}) créé<br>";
    } else {
        $error_code = $conn->errno;
        if ($error_code == 1062) {
            echo "⚠️ {$user['username']} ({$user['role']}) existe déjà<br>";
        } else {
            echo "✗ Erreur {$user['username']} : " . $stmt_user->error . "<br>";
        }
    }
}

$stmt_user->close();

// 2. GESTION DES UTILISATEURS (optionnel via GET)
if (isset($_GET['create_user'])) {
    $nom = $_GET['nom'] ?? 'exemple';
    $email = $_GET['email'] ?? 'exemple@email.com';
    $motdepasse = $_GET['motdepasse'] ?? '123456';
    $role = $_GET['role'] ?? 'user';
    $hash = password_hash($motdepasse, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, role, email, password_hash) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nom, $role, $email, $hash);

    if ($stmt->execute()) {
        echo "Utilisateur dynamique ajouté.<br>";
    } else {
        echo "Erreur user dynamique : " . $stmt->error . "<br>";
    }
    $stmt->close();
}

// 3. RÉCUPÉRATION DONNÉES CAPTEURS (inchangé)
if (
    isset($_GET['id_immeuble']) &&
    isset($_GET['temperature']) &&
    isset($_GET['humidite']) &&
    isset($_GET['pression'])
) {
    $id_immeuble = intval($_GET['id_immeuble']);
    $temperature = floatval($_GET['temperature']);
    $humidite = floatval($_GET['humidite']);
    $pression = floatval($_GET['pression']);

    $stmt = $conn->prepare("INSERT INTO donnees_capteurs (id_immeuble, temperature, humidite, pression) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iddd", $id_immeuble, $temperature, $humidite, $pression);

    if ($stmt->execute()) {
        echo "✓ Données capteurs : T={$temperature}°C, H={$humidite}%, P={$pression}hPa<br>";
    } else {
        echo "Erreur capteurs : " . $stmt->error . "<br>";
    }
    $stmt->close();
} else {
    echo "Paramètres capteurs manquants.<br>";
}

// Fermeture
$conn->close();

echo "<br><strong>Identifiants créés :</strong><br>";
echo "- Admin : mathys@admin.fr / admin123<br>";
echo "- User1 : anto@test.fr / anto1234<br>";
echo "- User2 : sacha@test.fr / sacha1234<br>";
?>
