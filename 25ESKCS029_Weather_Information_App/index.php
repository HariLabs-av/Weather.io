<?php
require_once __DIR__ . '/auth/auth.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weather.io</title>
    <link rel="icon" type="image/png" href="assests/icon.png">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');

        html {
            font-family: 'Roboto', sans-serif;
        }
    </style>
    <link rel="stylesheet" href="partial/darkstyle.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>

<body>
    <div class="container-md py-3">

        <header>
            <?php include 'partial/header.php'; ?>
        </header>

        <main class="card border-0 shadow-sm my-4">
            <div class="card-body p-4">

                <div id="weatherError" class="alert alert-danger d-none" role="alert"></div>

                <div id="weatherContent">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h2 class="card-title h3 mb-0 fw-bold" id="cityName">Loading...</h2>
                            <small class="text-secondary" id="currentDate"></small>
                        </div>
                        <span
                            class="badge weather-condition fs-6 px-3 py-2 rounded-pill"
                            id="weatherCondition">
                            --
                        </span>
                    </div>

                    <div class="row align-items-center my-4">
                        <div class="col-md-6 col-12 text-center text-md-start">
                            <div class="d-flex align-items-baseline justify-content-center justify-content-md-start">
                                <span class="display-1 fw-bold" id="tempValue">--</span>
                                <span class="fs-1 text-secondary">°C</span>
                            </div>
                            <p class="text-secondary mb-1">
                                Feels like <strong id="feelsLikeValue">--°C</strong>
                            </p>
                            <p class="text-secondary small">
                                High: <strong id="highTempValue">--°C</strong> &bull; Low: <strong
                                    id="lowTempValue">--°C</strong>
                            </p>
                        </div>

                        <div class="col-md-6 col-12 text-center">
                            <img id="weatherIcon" src="" alt="Weather Icon" class="img-fluid"
                                style="max-width: 140px; display: none;">
                        </div>
                    </div>

                    <hr class="my-4 text-secondary opacity-25">

                    <div class="row g-3">
                        <div class="col-6 col-md-4 col-lg-2">
                            <div class="weather-stat p-3 rounded-3 text-center h-100">
                                <small class="text-secondary d-block mb-1">Humidity</small>
                                <span class="fw-bold fs-5" id="humidityValue">--%</span>
                            </div>
                        </div>

                        <div class="col-6 col-md-4 col-lg-2">
                            <div class="weather-stat p-3 rounded-3 text-center h-100">
                                <small class="text-secondary d-block mb-1">Wind Speed</small>
                                <span class="fw-bold fs-5" id="windValue">-- km/h</span>
                            </div>
                        </div>

                        <div class="col-6 col-md-4 col-lg-2">
                            <div class="weather-stat p-3 rounded-3 text-center h-100">
                                <small class="text-secondary d-block mb-1">Pressure</small>
                                <span class="fw-bold fs-5" id="pressureValue">-- hPa</span>
                            </div>
                        </div>

                        <div class="col-6 col-md-4 col-lg-2">
                            <div class="weather-stat p-3 rounded-3 text-center h-100">
                                <small class="text-secondary d-block mb-1">Visibility</small>
                                <span class="fw-bold fs-5" id="visibilityValue">--</span>
                            </div>
                        </div>

                        <div class="col-6 col-md-4 col-lg-2">
                            <div class="weather-stat p-3 rounded-3 text-center h-100">
                                <small class="text-secondary d-block mb-1">Sunrise</small>
                                <span class="fw-bold fs-5" id="sunriseValue">--:--</span>
                            </div>
                        </div>

                        <div class="col-6 col-md-4 col-lg-2">
                            <div class="weather-stat p-3 rounded-3 text-center h-100">
                                <small class="text-secondary d-block mb-1">Sunset</small>
                                <span class="fw-bold fs-5" id="sunsetValue">--:--</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>

        <footer>
            <?php include 'partial/footer.php'; ?>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>
    <script src="partial/darkstyle.js"></script>

    <script>
        // open-meteo use kar raha hu, api key nahi chahiye
        
        function displayError(message) {
            const errorElement = document.getElementById('weatherError');
            errorElement.textContent = `Weather unavailable. ${message}`;
            errorElement.classList.remove('d-none');
        }

        // codes mapping
        function getWeatherDetails(code, isDay) {
            const dayOrNight = isDay ? 'd' : 'n';
            const weatherCodes = {
                0: { desc: 'Clear sky', icon: `01${dayOrNight}` },
                1: { desc: 'Mainly clear', icon: `02${dayOrNight}` },
                2: { desc: 'Partly cloudy', icon: `03${dayOrNight}` }, //i use bootstrap for this
                3: { desc: 'Overcast', icon: `04${dayOrNight}` },
                45: { desc: 'Fog', icon: `50${dayOrNight}` },
                48: { desc: 'Depositing rime fog', icon: `50${dayOrNight}` },
                51: { desc: 'Light Drizzle', icon: `09${dayOrNight}` },
                53: { desc: 'Moderate Drizzle', icon: `09${dayOrNight}` },
                55: { desc: 'Dense Drizzle', icon: `09${dayOrNight}` },
                61: { desc: 'Slight Rain', icon: `10${dayOrNight}` },
                63: { desc: 'Moderate Rain', icon: `10${dayOrNight}` },
                65: { desc: 'Heavy Rain', icon: `10${dayOrNight}` },
                71: { desc: 'Slight Snow', icon: `13${dayOrNight}` },
                73: { desc: 'Moderate Snow', icon: `13${dayOrNight}` },
                75: { desc: 'Heavy Snow', icon: `13${dayOrNight}` },
                95: { desc: 'Thunderstorm', icon: `11${dayOrNight}` },
            };
            return weatherCodes[code] || { desc: 'Unknown', icon: `01${dayOrNight}` };
        }

        function formatTime(isoString) {
            const date = new Date(isoString);
            return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }

        async function fetchWeatherData(city = 'Jaipur') {
            try {
                // lat-long find karo pehle
                const geoUrl = `https://geocoding-api.open-meteo.com/v1/search?name=${encodeURIComponent(city)}&count=1&language=en&format=json`;
                const geoResponse = await fetch(geoUrl);
                if (!geoResponse.ok) {
                    throw new Error('The weather service could not be reached. Please try again.');
                }
                const geoData = await geoResponse.json();

                if (!geoData.results || geoData.results.length === 0) {
                    throw new Error('City not found. Please check the spelling.');
                }

                const location = geoData.results[0];

                // ab weather fetch karo
                const weatherUrl = `https://api.open-meteo.com/v1/forecast?latitude=${location.latitude}&longitude=${location.longitude}&current=temperature_2m,relative_humidity_2m,apparent_temperature,is_day,weather_code,surface_pressure,wind_speed_10m&daily=temperature_2m_max,temperature_2m_min,sunrise,sunset&timezone=auto`;
                
                const weatherResponse = await fetch(weatherUrl);
                if (!weatherResponse.ok) {
                    throw new Error('Failed to fetch weather data.');
                }
                
                const weatherData = await weatherResponse.json();
                
                // data set kardo
                updateWeatherUI(location, weatherData);
                
            } catch (error) {
                displayError(error instanceof TypeError ? 'Check your internet connection and try again.' : error.message);
            }
        }

        function updateWeatherUI(location, weatherData) {
            const current = weatherData.current;
            const daily = weatherData.daily;
            
            document.getElementById('cityName').textContent = `${location.name}, ${location.country}`;
            document.getElementById('currentDate').textContent = new Date().toLocaleDateString('en-US', {
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
            });
            
            const weatherDetails = getWeatherDetails(current.weather_code, current.is_day);
            document.getElementById('weatherCondition').textContent = weatherDetails.desc;
            
            document.getElementById('tempValue').textContent = Math.round(current.temperature_2m);
            document.getElementById('feelsLikeValue').textContent = `${Math.round(current.apparent_temperature)}°C`;
            document.getElementById('highTempValue').textContent = `${Math.round(daily.temperature_2m_max[0])}°C`;
            document.getElementById('lowTempValue').textContent = `${Math.round(daily.temperature_2m_min[0])}°C`;
            
            document.getElementById('humidityValue').textContent = `${current.relative_humidity_2m}%`;
            document.getElementById('windValue').textContent = `${current.wind_speed_10m} km/h`;
            document.getElementById('pressureValue').textContent = `${Math.round(current.surface_pressure)} hPa`;
            
            document.getElementById('visibilityValue').textContent = `N/A`; 
            
            document.getElementById('sunriseValue').textContent = formatTime(daily.sunrise[0]);
            document.getElementById('sunsetValue').textContent = formatTime(daily.sunset[0]);
            
            const weatherIcon = document.getElementById('weatherIcon');
            weatherIcon.src = `https://openweathermap.org/img/wn/${weatherDetails.icon}@4x.png`;
            weatherIcon.style.display = 'block';
            
            // hide error
            document.getElementById('weatherError').classList.add('d-none');
        }

        document.addEventListener('DOMContentLoaded', () => {
            // start me jaipur
            fetchWeatherData('Jaipur');

            const searchForm = document.querySelector('header form[role="search"]');
            if (searchForm) {
                searchForm.addEventListener('submit', (e) => {
                    e.preventDefault(); 
                    const searchInput = searchForm.querySelector('input[type="search"]');
                    const city = searchInput.value.trim();
                    if (city) {
                        fetchWeatherData(city);
                    }
                });
            }
        });
    </script>
</body>

</html>