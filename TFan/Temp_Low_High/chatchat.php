<?php
$servername = "fdb1030.awardspace.net";
$username = "4546376_database";
$password = "fanonoff1234";
$databasename = "4546376_database";
$port = 3306;

$conn = new mysqli($servername, $username, $password, $databasename, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$temperature = $_GET['temperature'];
$relay = $_GET['relay'];

$sql = "INSERT INTO relaycontrol (temperature, relay) VALUES ('$temperature', '$relay')";

if ($conn->query($sql) === TRUE) {
    echo "Data inserted";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
