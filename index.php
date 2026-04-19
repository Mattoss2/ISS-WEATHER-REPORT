<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Station météo</title>

    <style>
        body {
            font-family: sans-serif;
            background: #121212;
            color: #eee;
            padding-top: 50px;
        }

        /*  Bandeau */
        .banner {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: #1e1e1e;
            color: #fff;
            text-align: center;
            padding: 10px;
            font-weight: bold;
            z-index: 1000;
        }

        /* Boutons filtres */
        .filters {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            padding: 1rem;
        }

        .filter-btn {
            padding: 0.5rem 1.2rem;
            border: 2px solid #444;
            border-radius: 20px;
            background: #1e1e1e;
            color: #eee;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .filter-btn:hover {
            border-color: #888;
        }

        .filter-btn.active {
            background: #2979ff;
            border-color: #2979ff;
            color: #fff;
        }

        /* Cards en ligne */
        .cards-container {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            padding: 1rem;
            justify-content: center;
        }

        .card {
            padding: 1rem 1.5rem;
            background: #1e1e1e;
            border-radius: 8px;
            flex: 1 1 300px;
            max-width: 400px;
        }

        .value {
            font-size: 2rem;
            font-weight: bold;
        }

        .label {
            font-size: 0.9rem;
            color: #aaa;
        }
    </style>
</head>

<body>

    <!--Bandeau -->
    <div class="banner">
        ☀ Station météo ☔
    </div>

    <!-- Boutons filtres -->
    <div class="filters">
        <button class="filter-btn active" data-filter="both">Tout</button>
        <button class="filter-btn" data-filter="temp">°C Température</button>
        <button class="filter-btn" data-filter="hum">% Humidité</button>
    </div>

    <!-- Cards -->
    <div class="cards-container">

        <div class="card">
            <div class="temp-block">
                <div class="label">Température</div>
                <div id="temp-value" class="value">-- °C</div>
            </div>

            <div class="hum-block" style="margin-top:1rem;">
                <div class="label">Humidité</div>
                <div id="hum-value" class="value">-- %</div>
            </div>

            <div style="margin-top:1rem;">
                <div class="label">Dernière mesure</div>
                <div id="timestamp" class="label">--</div>
            </div>
        </div>

        <div class="card">
            <div class="label">Rechercher une date et une heure</div>
            <input type="datetime-local" id="search-datetime">
            <button id="search-btn">Rechercher</button>

            <div class="temp-block" style="margin-top:1rem;">
                <div class="label">Température</div>
                <div id="search-temp" class="value">-- °C</div>
            </div>

            <div class="hum-block" style="margin-top:1rem;">
                <div class="label">Humidité</div>
                <div id="search-hum" class="value">-- %</div>
            </div>

            <div style="margin-top:1rem;">
                <div class="label">Mesure trouvée</div>
                <div id="search-time" class="label">--</div>
            </div>
        </div>

    </div>

    <!-- Script : dernière mesure -->
    <script>
        const tempEl = document.getElementById('temp-value');
        const humEl = document.getElementById('hum-value');
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

    <!-- Script : limite max datetime -->
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

    <!-- Script : recherche par date -->
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

    <!-- Script : filtres -->
    <script>
        const filterBtns = document.querySelectorAll('.filter-btn');

        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                filterBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                const filter = btn.dataset.filter;

                document.querySelectorAll('.temp-block').forEach(el => {
                    el.style.display = (filter === 'hum') ? 'none' : 'block';
                });

                document.querySelectorAll('.hum-block').forEach(el => {
                    el.style.display = (filter === 'temp') ? 'none' : 'block';
                });
            });
        });
    </script>

</body>
</html>
