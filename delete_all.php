<?php
$conn = new mysqli("localhost", "root", "", "nametag");

if ($conn->connect_error) {
    die("Database connection failed.");
}

$conn->query("TRUNCATE TABLE delage");

$conn->close();

header("Location: index.php");
exit;