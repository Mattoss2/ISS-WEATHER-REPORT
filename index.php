<!-- index.php (exemple minimal) -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Station météo</title>
    <style>
        body { font-family: sans-serif; background: #121212; color: #eee; }
        .card { padding: 1rem 1.5rem; margin: 1rem auto; max-width: 400px; background: #1e1e1e; border-radius: 8px; }
        .value { font-size: 2rem; font-weight: bold; }
        .label { font-size: 0.9rem; color: #aaa; }
    </style>
</head>
<body>

<div class="card">
    <div class="label">Température</div>
    <div id="temp-value" class="value">-- °C</div>

    <div class="label" style="margin-top:1rem;">Humidité</div>
    <div id="hum-value" class="value">-- %</div>

    <div class="label" style="margin-top:1rem;">Dernière mesure</div>
    <div id="timestamp" class="label">--</div>
</div>

<script>
const tempEl = document.getElementById('temp-value');
const humEl  = document.getElementById('hum-value');
const timeEl = document.getElementById('timestamp');

async function fetchLatest() {
    try {
        const response = await fetch('affichage.php?ts=' + Date.now(), { cache: 'no-store' });
        const data = await response.json();

        console.log("Réponse affichage.php :", data);

        if (data.success) {
            tempEl.textContent = Number(data.temperature).toFixed(1) + ' °C';
            humEl.textContent = Number(data.humidite).toFixed(1) + ' %';
            timeEl.textContent = data.date_mesure;
        } else {
            console.log("Erreur côté PHP :", data);
        }
    } catch (err) {
        console.error("Erreur fetch :", err);
    }
}

fetchLatest();
setInterval(fetchLatest, 5000);
</script>

</body>
</html>