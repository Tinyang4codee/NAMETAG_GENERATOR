<?php
session_start();

$conn = new mysqli("localhost", "root", "", "nametag");

if (!isset($_SESSION['fellowship_date'])) {
    die("Fellowship date not set.");
}

$FirstName = trim($_POST['FirstName']);
$Campus    = trim($_POST['Campus']);
$date      = $_SESSION['fellowship_date'];

$stmt = $conn->prepare(
    "INSERT INTO delage (FirstName, Campus, fellowship_date)
     VALUES (?, ?, ?)"
);
$stmt->bind_param("sss", $FirstName, $Campus, $date);
$stmt->execute();

$stmt->close();
$conn->close();

header("Location: index.php");
exit;