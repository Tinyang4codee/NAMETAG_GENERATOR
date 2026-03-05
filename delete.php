<?php
$conn = new mysqli("localhost", "root", "", "nametag");

if (!isset($_GET['delegateID']) || !is_numeric($_GET['delegateID'])) {
    die("No delegate selected.");
}

$delegateID = (int)$_GET['delegateID'];

$stmt = $conn->prepare("DELETE FROM delage WHERE delegateID = ?");
$stmt->bind_param("i", $delegateID);
$stmt->execute();

$stmt->close();
$conn->close();

header("Location: index.php");
exit;