<?php
$conn = new mysqli("fdb1030.awardspace.net", "4624843_mine", "Leepal123", "4624843_mine", 3306);

// Save data from ESP8266
if (isset($_GET['temperature']) && isset($_GET['humidity']) && isset($_GET['relay']) && isset($_GET['mode'])) {
    $t = $_GET['temperature'];
    $h = $_GET['humidity'];
    $r = $_GET['relay'];
    $m = $_GET['mode'];
    $conn->query("INSERT INTO relaycontrol (temperature, humidity, relay, mode) VALUES ('$t', '$h', '$r', '$m')");
    echo "Data saved!";
    exit();
}

// Read latest sensor data
$latest = $conn->query("SELECT * FROM relaycontrol ORDER BY id DESC LIMIT 1")->fetch_assoc();
$t = $latest ? $latest['temperature'] : 0;
$h = $latest ? $latest['humidity'] : 0;

// Manual control
if (isset($_POST['fan_on'])) {
    file_put_contents("manual.txt", "on");
    $conn->query("INSERT INTO relaycontrol (temperature, humidity, relay, mode) VALUES ('$t', '$h', 0, 'manual')");
}
if (isset($_POST['fan_off'])) {
    file_put_contents("manual.txt", "off");
    $conn->query("INSERT INTO relaycontrol (temperature, humidity, relay, mode) VALUES ('$t', '$h', 1, 'manual')");
}
if (isset($_POST['auto'])) {
    $relayState = ($t >= 28) ? 0 : 1;
    $conn->query("INSERT INTO relaycontrol (temperature, humidity, relay, mode) VALUES ('$t', '$h', '$relayState', 'auto')");
    file_put_contents("manual.txt", "auto");
}

// Get current mode and logs
$manual = file_exists("manual.txt") ? trim(file_get_contents("manual.txt")) : "auto";
$latest = $conn->query("SELECT * FROM relaycontrol ORDER BY id DESC LIMIT 1")->fetch_assoc();
$logs = $conn->query("SELECT * FROM relaycontrol ORDER BY id DESC LIMIT 10");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fan Monitoring System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
      
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }
        
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
        }
        
        h2 {
            background-color: #3498db;
            color: white;
            padding: 12px 15px;
            border-radius: 5px;
            margin-top: 30px;
            font-size: 1.3rem;
        }
        
        /* Data Display */
        .data-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .data-card {
            flex: 1;
            min-width: 200px;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .data-card strong {
            display: block;
            color: #7f8c8d;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }
        
        .data-card span {
            font-size: 1.2rem;
            font-weight: bold;
        }
        
        .relay-on {
            color: #27ae60;
        }
        
        .relay-off {
            color: #e74c3c;
        }
        

        .control-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 30px;
        }
        
        button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            flex: 1;
            min-width: 120px;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background-color: #2980b9;
        }
        
        button[name="fan_on"] {
            background-color: #27ae60;
        }
        
        button[name="fan_on"]:hover {
            background-color: #219653;
        }
        
        button[name="fan_off"] {
            background-color: #e74c3c;
        }
        
        button[name="fan_off"]:hover {
            background-color: #c0392b;
        }
        

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }
        
        th {
            background-color: #3498db;
            color: white;
            font-weight: 600;
        }
        
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        tr:hover {
            background-color: #f1f7fd;
        }
                
		.chart-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            margin: 30px 0;
        }
        
        .chart-wrapper {
            flex: 1 1 45%;
            min-width: 300px;
            max-width: 500px;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .chart-wrapper canvas {
            width: 100% !important;
            height: 300px !important;
        }
        
        @media (max-width: 768px) {
            .chart-wrapper {
                flex: 1 1 100%;
            }
            
            .data-card {
                min-width: 100%;
            }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h1>Temperature-Controlled Fan Monitoring System</h1>

    <h2>Current Status</h2>
    <div class="data-container">
        <div class="data-card">
            <strong>Temperature</strong>
            <span><?= $latest ? number_format($latest['temperature'], 2) . ' °C' : 'N/A' ?></span>
        </div>
        <div class="data-card">
            <strong>Humidity</strong>
            <span><?= $latest ? number_format($latest['humidity'], 2) . ' %' : 'N/A' ?></span>
        </div>
        <div class="data-card">
            <strong>Fan Status</strong>
            <span class="<?= $latest && $latest['relay'] == 0 ? 'relay-on' : 'relay-off' ?>">
                <?= $latest ? ($latest['relay'] == 0 ? 'ON' : 'OFF') : 'N/A' ?>
            </span>
        </div>
        <div class="data-card">
            <strong>Mode</strong>
            <span><?= strtoupper($manual ?? 'AUTO') ?></span>
        </div>
        <div class="data-card">
            <strong>Last Update</strong>
            <span><?= $latest ? $latest['recordate'] : 'N/A' ?></span>
        </div>
    </div>

    <h2>Control Panel</h2>
    <form method="POST" class="control-buttons">
        <button name="fan_on">Turn Fan ON</button>
        <button name="fan_off">Turn Fan OFF</button>
        <button name="auto">Auto Mode</button>
    </form>

    <h2>Recent Activity</h2>
    <table>
        <tr>
            <th>Time</th>
            <th>Temp (°C)</th>
            <th>Humidity (%)</th>
            <th>Fan</th>
            <th>Mode</th>
        </tr>
        <?php while ($row = $logs->fetch_assoc()): ?>
        <tr>
            <td><?= $row['recordate'] ?></td>
            <td><?= number_format($row['temperature'], 2) ?></td>
            <td><?= number_format($row['humidity'], 2) ?></td>
            <td class="<?= $row['relay'] == 0 ? 'relay-on' : 'relay-off' ?>">
                <?= $row['relay'] == 0 ? 'ON' : 'OFF' ?>
            </td>
            <td><?= ucfirst($row['mode'] ?? 'auto') ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <h2>Data Visualization</h2>
    <div class="chart-container">
        <div class="chart-wrapper">
            <canvas id="tempChart" aria-label="Temperature Chart" role="img"></canvas>
        </div>
        <div class="chart-wrapper">
            <canvas id="humidityChart" aria-label="Humidity Chart" role="img"></canvas>
        </div>
        <div class="chart-wrapper">
            <canvas id="fanStatusChart" aria-label="Fan Status Chart" role="img"></canvas>
        </div>
        <div class="chart-wrapper">
            <canvas id="modeChart" aria-label="Mode Chart" role="img"></canvas>
        </div>
    </div>

    <script>
        let labels = [<?php
            $conn->query("SET time_zone = '+07:00';");
            $data = $conn->query("SELECT * FROM relaycontrol ORDER BY id DESC LIMIT 10");
            $times = []; $temps = []; $humidity = []; $fan = []; $modes = [];
            while($row = $data->fetch_assoc()){
                $times[] = '"' . $row['recordate'] . '"';
                $temps[] = $row['temperature'];
                $humidity[] = $row['humidity'];
                $fan[] = $row['relay'];
                $modes[] = '"' . $row['mode'] . '"';
            }
            echo implode(',', $times);
        ?>];

        let temperatures = [<?= implode(',', $temps) ?>];
        let humidities = [<?= implode(',', $humidity) ?>];
        let fanStatus = [<?= implode(',', $fan) ?>];
        let modes = [<?= implode(',', $modes) ?>];

        // Temperature Chart
        new Chart(document.getElementById('tempChart'), {
            type: 'line',
            data: {
                labels: labels.reverse(),
                datasets: [{
                    label: 'Temperature (°C)',
                    data: temperatures.reverse(),
                    borderColor: '#e74c3c',
                    backgroundColor: 'rgba(231, 76, 60, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Temperature Trend',
                        font: {
                            size: 16
                        }
                    },
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false
                    }
                }
            }
        });

        // Humidity Chart
        new Chart(document.getElementById('humidityChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Humidity (%)',
                    data: humidities.reverse(),
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Humidity Trend',
                        font: {
                            size: 16
                        }
                    },
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false
                    }
                }
            }
        });

        // Fan Status Chart
        let fanOn = fanStatus.filter(x => x == 0).length;
        let fanOff = fanStatus.filter(x => x == 1).length;
        new Chart(document.getElementById('fanStatusChart'), {
            type: 'doughnut',
            data: {
                labels: ['Fan ON', 'Fan OFF'],
                datasets: [{
                    data: [fanOn, fanOff],
                    backgroundColor: ['#27ae60', '#e74c3c'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Fan Status Distribution',
                        font: {
                            size: 16
                        }
                    }
                }
            }
        });

        // Mode Chart
        let manualMode = modes.filter(x => x.toLowerCase().includes('manual')).length;
        let autoMode = modes.filter(x => x.toLowerCase().includes('auto')).length;
        new Chart(document.getElementById('modeChart'), {
            type: 'pie',
            data: {
                labels: ['Manual', 'Auto'],
                datasets: [{
                    data: [manualMode, autoMode],
                    backgroundColor: ['#f39c12', '#3498db'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Operation Mode Distribution',
                        font: {
                            size: 16
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>