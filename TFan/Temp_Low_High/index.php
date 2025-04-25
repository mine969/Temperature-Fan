<?php
// Connect to MySQL
$conn = new mysqli("fdb1030.awardspace.net", "4624843_mine", "Leepal123", "4624843_mine", 3306);

// 1. Save data from ESP8266
if (isset($_GET['temperature']) && isset($_GET['humidity']) && isset($_GET['relay'])) {
    $t = $_GET['temperature'];
    $h = $_GET['humidity'];
    $r = $_GET['relay'];

    $conn->query("INSERT INTO relaycontrol (temperature, humidity, relay) VALUES ('$t', '$h', '$r')");
    echo "Data saved!";
    exit();
}

// 2. Read latest sensor data
$latest = $conn->query("SELECT * FROM relaycontrol ORDER BY id DESC LIMIT 1")->fetch_assoc();
$t = $latest ? $latest['temperature'] : 0;
$h = $latest ? $latest['humidity'] : 0;

// 3. Handle manual control (and log it too)
if (isset($_POST['fan_on'])) {
    file_put_contents("manual.txt", "on");
    $conn->query("INSERT INTO relaycontrol (temperature, humidity, relay) VALUES ('$t', '$h', 0)");
}
if (isset($_POST['fan_off'])) {
    file_put_contents("manual.txt", "off");
    $conn->query("INSERT INTO relaycontrol (temperature, humidity, relay) VALUES ('$t', '$h', 1)");
}
if (isset($_POST['auto'])) {
    file_put_contents("manual.txt", "auto");
}

// 4. Get updated manual mode
$manual = file_exists("manual.txt") ? trim(file_get_contents("manual.txt")) : "auto";

// 5. Reload latest data after log
$latest = $conn->query("SELECT * FROM relaycontrol ORDER BY id DESC LIMIT 1")->fetch_assoc();
$logs = $conn->query("SELECT * FROM relaycontrol ORDER BY id DESC LIMIT 10");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fan Monitoring System</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Temperature-Controlled Fan</h1>

    <h2>Latest Reading</h2>
    <?php if ($latest): ?>
        <p><strong>Temperature:</strong> <?= $latest['temperature'] ?> °C</p>
        <p><strong>Humidity:</strong> <?= $latest['humidity'] ?> %</p>
        <p><strong>Fan Status:</strong> <?= $latest['relay'] == 0 ? 'ON' : 'OFF' ?></p>
        <p><strong>Mode:</strong> <?= strtoupper($manual) ?></p>
        <p><strong>Time:</strong> <?= $latest['recordate'] ?></p>
    <?php else: ?>
        <p>No data available</p>
    <?php endif; ?>

    <h2>Manual Control</h2>
    <form method="POST">
        <button name="fan_on">Turn Fan ON</button>
        <button name="fan_off">Turn Fan OFF</button>
        <button name="auto">Auto Mode</button>
    </form>

    <h2>Last 10 Logs</h2>
    <table border="1" cellpadding="5">
        <tr>
            <th>Time</th>
            <th>Temperature (°C)</th>
            <th>Humidity (%)</th>
            <th>Fan</th>
        </tr>
        <?php while ($row = $logs->fetch_assoc()): ?>
        <tr>
            <td><?= $row['recordate'] ?></td>
            <td><?= $row['temperature'] ?></td>
            <td><?= $row['humidity'] ?></td>
            <td><?= $row['relay'] == 0 ? 'ON' : 'OFF' ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
