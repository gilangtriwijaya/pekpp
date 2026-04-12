<?php
$mysqli = @new mysqli('127.0.0.1', 'pekpp', 'Pekpp@2026', 'pekpp');
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo "mysqli_connect_error: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    exit;
}
echo "connected as: ";
$res = $mysqli->query("SELECT USER(), CURRENT_USER()");
$row = $res->fetch_row();
echo htmlspecialchars(json_encode($row));
$mysqli->close();
