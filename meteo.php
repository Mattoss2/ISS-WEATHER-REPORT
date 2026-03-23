<?php
$servername = "10.2.2.38";
$username   = "mathys";
$password   = "1234";
$database   = "meteo";

//créer la connexion
// Connexion correcte : utilise les variables, pas les valeurs directement
$conn = new mysqli($servername, $username, $password, $database);

// Vérification de la connexion
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

echo "Connexion réussie !";

// création database 

$sql = "CREATE DATABASE myDB";
if ($conn->querry($sql)=== TRUE ){
    echo "Database created successfully"
} else { echo "error creating database:" .$conn->error;
}

//fermer connexion 
$conn->close();
?>


<!DOCTYPE html>
<html lang = "en">
<head>
    
<link rel="stylesheet" href="">
    <meta charset="utf-8" />
    <title>ISS WEATHER REPORT</title>
</head>

<body>
    <header>
<div id="entete">
    <p>ISS WEATHER REPORT</p>
</div>
</header>

<div id="UI"> 
    <div name="press">Pression</div>
    <div name="temp">température</div>
    <div name="hygro">hydrométrie</div>
</div>
<ol>
    <li>relevé pression</li>
    <li>relevé température</li>
    <li>relevé hydrométrique (%)</li>
    <li>TOUT</li>
    <li>RESET</li>
</ol>
<main>
    <!-- ajoute un visuel interactif qui fait augmenter le termo l'hygro et le baro avec les relevés actuels-->
    <section id="meteomap">
        <div id=" thermo">
            <div class="nb_temp"> </div>
        </div>
         <div id=" pression">
            <div class="nb_press"> </div>
        </div>
 <div id=" Humidité">
            <div class="nb_hygro"> </div>
        </div>

    </section>
</main>

<ol id="filtres">
    <li>Capteurs</li>
    <li>État</li>
    <li>Humidité</li>
    <li> Journée</li>
    <li>Prévision</li>
    <li>Quitter</li>
    <li>Valider</li>
</ol>
</body>
<footer></footer>
</html>