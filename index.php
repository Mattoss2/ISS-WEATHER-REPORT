<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Station météo</title>
    <style>
        body { font-family: sans-serif; background: #121212; color: #eee; }
        .card { padding: 1rem 1.5rem; margin: 1rem auto; max-width: 400px; background: #1e1e1e; borde>
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

<div class="card">
    <div class="label">Rechercher une date et une heure</div>
    <input type="datetime-local" id="search-datetime">
    <button id="search-btn">Rechercher</button>

    <div class="label" style="margin-top:1rem;">Température</div>
    <div id="search-temp" class="value">-- °C</div>

    <div class="label" style="margin-top:1rem;">Humidité</div>
    <div id="search-hum" class="value">-- %</div>

    <div class="label" style="margin-top:1rem;">Mesure trouvée</div>
    <div id="search-time" class="label">--</div>
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

<script>
const datetimeInput = document.getElementById('search-datetime');

function getCurrentLocalDateTime() {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');

    return `${year}-${month}-${day}T${hours}:${minutes}`;
}

datetimeInput.max = getCurrentLocalDateTime();
</script>

<script>
const searchBtn = document.getElementById('search-btn');
const searchTemp = document.getElementById('search-temp');
const searchHum = document.getElementById('search-hum');
const searchTime = document.getElementById('search-time');

searchBtn.addEventListener('click', async () => {
    const selectedDateTime = datetimeInput.value;

    if (!selectedDateTime) {
        alert("Choisis une date et une heure");
        return;
    }

    try {
        const response = await fetch(
            'search_by_datetime.php?datetime=' + encodeURIComponent(selectedDateTime),
            { cache: 'no-store' }
        );

        const data = await response.json();

        if (data.success) {
            searchTemp.textContent = Number(data.temperature).toFixed(1) + ' °C';
            searchHum.textContent = Number(data.humidite).toFixed(1) + ' %';
            searchTime.textContent = `Mesure trouvée : ${data.date_mesure}`;
        } else {
            searchTemp.textContent = '-- °C';
            searchHum.textContent = '-- %';
            searchTime.textContent = data.message || 'Aucune donnée';
        }
    } catch (err) {
        console.error(err);
        searchTime.textContent = 'Erreur lors de la recherche';
    }
});
</script>

</body>
</html>