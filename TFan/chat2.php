<?php
        $servername = "fdb1030.awardspace.net";
        $username = "4546376_database";
        $password = "fanonoff1234";
        $databasename = "4546376_database";
        $port = 3306;

        $conn = new mysqli($servername, $username, $password, $databasename, $port);

        if ($conn->connect_error) {
            die("<p>Connection failed: " . $conn->connect_error . "</p>");
        }

        $sql = "SELECT temperature, relay FROM relaycontrol ORDER BY id DESC LIMIT 1";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo "<p>Current Temperature:</p>";
            echo "<div class='data'>" . $row['temperature'] . " °C</div>";
            echo "<p>Relay Status:</p>";
            echo "<div class='data " . ($row['relay'] == 1 ? "relay-on'>ON" : "relay-off'>OFF") . "</div>";
        } else {
            echo "<p>No data available</p>";
        }

        $conn->close();
        ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Temperature Monitor</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            background-color: rgb(27, 25, 25);
            color: rgb(17, 233, 17);
            font-family: 'Times New Roman', Times, serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        h1 {
            margin-top: 20px;
            font-size: 2.5em;
            text-align: center;
        }
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 30px;
            width: 60%;
            padding: 20px;
            border: 1px solid rgb(17, 233, 17);
            border-radius: 10px;
            background-color: rgb(40, 40, 40);
        }
        .data {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
            text-align: center;
        }
        .relay-on {
            color: green;
        }
        .relay-off {
            color: red;
        }
    </style>
</head>
<body>
    <h1>Temperature Monitoring System</h1>
    <div class="container">
       
    </div>
</body>
</html>
