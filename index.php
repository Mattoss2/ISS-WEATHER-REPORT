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
            margin: 0;
        }

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

        .filters {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            padding: 1rem;
            flex-wrap: wrap;
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

        .chart-container {
            max-width: 900px;
            margin: 2rem auto;
            background: #1e1e1e;
            padding: 1rem;
            border-radius: 8px;
        }

        .chart-wrapper {
            position: relative;
            width: 100%;
            height: 400px;
            margin-top: 1rem;
        }

        #weatherChart {
            display: block;
            width: 100% !important;
            height: 100% !important;
        }

        .date-range {
            display: flex;
            justify-content: center;
            gap: 1rem;
            align-items: center;
            margin-top: 1rem;
            flex-wrap: wrap;
        }

        .date-range input[type="date"],
        input[type="datetime-local"] {
            background: #121212;
            border: 1px solid #444;
            color: #eee;
            padding: 0.3rem 0.5rem;
            border-radius: 4px;
            margin-top: 0.4rem;
        }

        .date-range button,
        #search-btn {
            padding: 0.4rem 1rem;
            border-radius: 4px;
            border: none;
            background: #2979ff;
            color: #fff;
            cursor: pointer;
            margin-top: 0.4rem;
        }

        @media (max-width: 768px) {
            .chart-wrapper {
                height: 320px;
            }
        }
    </style>
</head>

<body>

    <div class="banner">
        ☀ Station météo ☔
    </div>

    <div class="filters">
        <button class="filter-btn active" data-filter="both">Tout</button>
        <button class="filter-btn" data-filter="temp">°C Température</button>
        <button class="filter-btn" data-filter="hum">% Humidité</button>
    </div>

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

    <div class="chart-container">
        <div class="label" style="margin-bottom:0.5rem;">Graphique sur une période</div>
        <div class="date-range">
            <div>
                <span class="label">Début</span><br>
                <input type="date" id="start-date">
            </div>
            <div>
                <span class="label">Fin</span><br>
                <input type="date" id="end-date">
            </div>
            <div>
                <button id="load-chart">Afficher le graphique</button>
            </div>
        </div>
        <div class="chart-wrapper">
            <canvas id="weatherChart"></canvas>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const tempEl = document.getElementById('temp-value');
        const humEl = document.getElementById('hum-value');
        const timeEl = document.getElementById('timestamp');

        async function fetchLatest() {
            try {
                const response = await fetch('affichage.php?ts=' + Date.now(), { cache: 'no-store' });
                const data = await response.json();

                if (data.success) {
                    tempEl.textContent = Number(data.temperature).toFixed(1) + ' °C';
                    humEl.textContent = Number(data.humidite).toFixed(1) + ' %';
                    timeEl.textContent = data.date_mesure;
                } else {
                    console.log('Erreur côté PHP :', data);
                }
            } catch (err) {
                console.error('Erreur fetch :', err);
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
                alert('Choisis une date et une heure');
                return;
            }

            try {
                const response = await fetch('search_by_datetime.php?datetime=' + encodeURIComponent(selectedDateTime), { cache: 'no-store' });
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

    <script>
        const startDateInput = document.getElementById('start-date');
        const endDateInput = document.getElementById('end-date');
        const loadChartBtn = document.getElementById('load-chart');
        const chartCanvas = document.getElementById('weatherChart');
        const ctx = chartCanvas.getContext('2d');
        const filterBtns = document.querySelectorAll('.filter-btn');
        let weatherChartInstance = null;
        let currentFilter = 'both';

        function applyChartFilter(filter) {
            if (!weatherChartInstance) return;

            if (filter === 'both') {
                weatherChartInstance.setDatasetVisibility(0, true);
                weatherChartInstance.setDatasetVisibility(1, true);
            } else if (filter === 'temp') {
                weatherChartInstance.setDatasetVisibility(0, true);
                weatherChartInstance.setDatasetVisibility(1, false);
            } else if (filter === 'hum') {
                weatherChartInstance.setDatasetVisibility(0, false);
                weatherChartInstance.setDatasetVisibility(1, true);
            }

            weatherChartInstance.update();
        }

        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                filterBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                const filter = btn.dataset.filter;
                currentFilter = filter;

                document.querySelectorAll('.temp-block').forEach(el => {
                    el.style.display = (filter === 'hum') ? 'none' : 'block';
                });

                document.querySelectorAll('.hum-block').forEach(el => {
                    el.style.display = (filter === 'temp') ? 'none' : 'block';
                });

                applyChartFilter(filter);
            });
        });

        function getTodayDate() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        const today = getTodayDate();
        endDateInput.value = today;
        startDateInput.value = today;
        endDateInput.max = today;
        startDateInput.max = today;

        async function loadChartData() {
            const start = startDateInput.value;
            const end = endDateInput.value;

            if (!start || !end) {
                alert('Sélectionne une date de début et de fin');
                return;
            }

            if (start > end) {
                alert('La date de début doit être avant la date de fin');
                return;
            }

            try {
                const response = await fetch(`data.php?start=${encodeURIComponent(start)}&end=${encodeURIComponent(end)}`);
                const data = await response.json();

                if (!data.success) {
                    alert(data.message || 'Erreur lors du chargement des données');
                    return;
                }

                const labels = data.points.map(p => p.date_mesure);
                const tempData = data.points.map(p => parseFloat(p.temperature));
                const humData = data.points.map(p => parseFloat(p.humidite));

                if (weatherChartInstance) {
                    weatherChartInstance.destroy();
                    weatherChartInstance = null;
                }

                weatherChartInstance = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Température (°C)',
                                data: tempData,
                                borderColor: 'rgba(255, 99, 132, 1)',
                                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                tension: 0.2,
                                yAxisID: 'y-temp'
                            },
                            {
                                label: 'Humidité (%)',
                                data: humData,
                                borderColor: 'rgba(54, 162, 235, 1)',
                                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                tension: 0.2,
                                yAxisID: 'y-hum'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        stacked: false,
                        scales: {
                            x: {
                                ticks: {
                                    color: '#eee'
                                },
                                grid: {
                                    color: 'rgba(255,255,255,0.08)'
                                }
                            },
                            'y-temp': {
                                type: 'linear',
                                position: 'left',
                                ticks: {
                                    color: '#ff6384'
                                },
                                grid: {
                                    color: 'rgba(255,255,255,0.08)'
                                }
                            },
                            'y-hum': {
                                type: 'linear',
                                position: 'right',
                                ticks: {
                                    color: '#36a2eb'
                                },
                                grid: {
                                    drawOnChartArea: false
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                labels: {
                                    color: '#eee'
                                }
                            }
                        }
                    }
                });

                applyChartFilter(currentFilter);
            } catch (e) {
                console.error(e);
                alert('Erreur lors du chargement des données');
            }
        }

        loadChartBtn.addEventListener('click', loadChartData);
    </script>

</body>
</html>