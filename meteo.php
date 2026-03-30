<?php
$servername = "10.2.2.38";
$username = "mathys";
$password = "1234";
$database = "meteo";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Création des tables si elles n'existent pas
$tables = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
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

// 1. GESTION DES UTILISATEURS 
if (isset($_GET['create_user'])) {
    $nom = $_GET['nom'] ?? 'exemple';
    $email = $_GET['email'] ?? 'exemple@email.com';
    $motdepasse = $_GET['motdepasse'] ?? '123456';
    $hash = password_hash($motdepasse, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nom, $email, $hash);

    if ($stmt->execute()) {
        echo "Utilisateur ajouté.<br>";
    } else {
        echo "Erreur user : " . $stmt->error . "<br>";
    }
    $stmt->close();
}

// 2. RÉCUPÉRATION DONNÉES CAPTEURS (PRINCIPAL)
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
        echo "✓ Données capteurs enregistrées : T=" . $temperature . "°C, H=" . $humidite . "%, P=" . $pression . "hPa<br>";
    } else {
        echo "Erreur capteurs : " . $stmt->error . "<br>";
    }

    $stmt->close();
} else {
    echo "Paramètres capteurs manquants.<br>";
}

// Fermeture
$conn->close();
?>
