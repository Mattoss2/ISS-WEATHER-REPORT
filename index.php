<?php
session_start();

// Initialiser les variables
$connected = false;
$temperature = "--";
$humidite = "--";

// Gestion de la connexion
if(isset($_POST['login'])) {
    $user = $_POST['dbUser'];
    $pass = $_POST['dbPass'];

    // Connexion MySQL
    $conn = @new mysqli("localhost", $user, $pass, "iss_weather"); // Remplace "iss_weather" par le nom de ta base
    if($conn->connect_error) {
        $error = "Nom d'utilisateur ou mot de passe incorrect !";
    } else {
        $_SESSION['conn_user'] = $user;
        $_SESSION['conn_pass'] = $pass;
        $connected = true;
        $_SESSION['connected'] = true;
    }
}

// Déconnexion
if(isset($_POST['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Si connecté, récupérer les dernières données
if(isset($_SESSION['connected']) && $_SESSION['connected']) {
    $connected = true;
    $conn = @new mysqli("localhost", $_SESSION['conn_user'], $_SESSION['conn_pass'], "iss_weather");
    if(!$conn->connect_error) {
        $result = $conn->query("SELECT temperature, humidite FROM meteo ORDER BY id DESC LIMIT 1");
        if($row = $result->fetch_assoc()) {
            $temperature = $row['temperature'];
            $humidite = $row['humidite'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>ISS WEATHER REPORT</title>
    <style>
        body { font-family: Arial; margin:0; padding:0; background:#f3f3f3; color:#333; }
        header { background:#0044cc; color:white; text-align:center; padding:20px 0; }
        #entete p { margin:0; font-size:2em; }
        #UI { display:flex; justify-content:space-around; margin:20px 0; padding:10px; background:#fff; border-radius:8px; }
        #UI div { padding:10px; background:#0044cc; color:white; border-radius:8px; width:30%; text-align:center; cursor:pointer; }
        #UI div:hover { background:#0033aa; }
        ol { margin:20px; padding:0; list-style:none; }
        ol li { background:#0044cc; color:white; padding:10px; margin:5px 0; border-radius:4px; cursor:pointer; text-align:center; }
        ol li:hover { background:#0033aa; }
        main { padding:20px; }
        #meteomap { display:flex; justify-content:space-around; margin-top:20px; }
        #meteomap div { text-align:center; padding:20px; background:#fff; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.1); width:25%; }
        #meteomap .nb_temp, #meteomap .nb_hygro { font-size:1.5em; margin-top:10px; }
        .content { display:none; margin-top:20px; text-align:center; }
        footer { background:#0044cc; text-align:center; padding:10px; color:white; position:fixed; width:100%; bottom:0; }
        #dbBtn { background:#28a745; color:white; border:none; padding:10px 20px; border-radius:8px; cursor:pointer; margin-top:10px; }
        #loginForm { text-align:center; margin:20px 0; }
        #loginForm input { margin:5px; padding:8px; }
    </style>
</head>
<body>
    <header>
        <div id="entete">
            <p>ISS WEATHER REPORT</p>
        </div>
    </header>

    <!-- Formulaire connexion SQL -->
    <div id="loginForm">
        <?php if(!$connected): ?>
        <form method="post">
            <input type="text" name="dbUser" placeholder="Nom d'utilisateur" required>
            <input type="password" name="dbPass" placeholder="Mot de passe" required>
            <button type="submit" name="login">Se connecter</button>
        </form>
        <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <?php else: ?>
        <form method="post">
            <button type="submit" name="logout">Se déconnecter</button>
        </form>
        <?php endif; ?>
    </div>

    <div id="UI"> 
        <div id="temp" onclick="showContent('temp')">Température en direct</div>
        <div id="hygro" onclick="showContent('hygro')">Humidité en direct</div>
    </div>

    <main>
        <section id="meteomap">
            <div id="thermo">
                <div class="nb_temp"><?= $temperature ?>°C</div>
            </div>
            <div id="humidite">
                <div class="nb_hygro"><?= $humidite ?>%</div>
            </div>
        </section>
        
        <div id="content" class="content">
            <p id="tempContent">Voici les détails de la température.</p>
            <p id="hygroContent">Voici les détails de l'humidité.</p>
            <p id="journéeContent">Voici le bilan de la journée.</p>
            <p id="moisContent">Voici le bilan du mois actuel.</p>
            <p id="anneContent">Voici le bilan de l'année actuel.</p>
        </div>
    </main>
    
    <ol>
        <li onclick="showContent('temp')">Relevé Température</li>
        <li onclick="showContent('hygro')">Relevé D'Humidité (%)</li>
        <li onclick="showContent('journée')">Bilan de la journée</li>
        <li onclick="showContent('mois')">Bilan du mois actuel</li>
        <li onclick="showContent('anne')">Bilan de l'année actuel</li>
    </ol>

    <footer>
        <p>ISS Weather Data © 2026</p>
    </footer>

    <script>
        function showContent(type) {
            document.getElementById("content").style.display = "block";
            const sections = ['tempContent','hygroContent','journéeContent','moisContent','anneContent'];
            sections.forEach(s => document.getElementById(s).style.display = 'none');
            if(type==='temp') document.getElementById("tempContent").style.display='block';
            else if(type==='hygro') document.getElementById("hygroContent").style.display='block';
            else if(type==='journée') document.getElementById("journéeContent").style.display='block';
            else if(type==='mois') document.getElementById("moisContent").style.display='block';
            else if(type==='anne') document.getElementById("anneContent").style.display='block';
        }
    </script>
</body>
</html>