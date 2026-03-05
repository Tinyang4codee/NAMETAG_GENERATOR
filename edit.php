<?php
$conn = new mysqli("localhost", "root", "", "nametag");

if (!isset($_GET['delegateID']) || !is_numeric($_GET['delegateID'])) {
    die("No delegate selected.");
}

$delegateID = (int)$_GET['delegateID'];

$stmt = $conn->prepare("SELECT FirstName, Campus FROM delage WHERE delegateID = ?");
$stmt->bind_param("i", $delegateID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Delegate not found.");
}

$data = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $FirstName = trim($_POST['FirstName']);
    $Campus    = trim($_POST['Campus']);

    $update = $conn->prepare(
        "UPDATE delage SET FirstName = ?, Campus = ? WHERE delegateID = ?"
    );
    $update->bind_param("ssi", $FirstName, $Campus, $delegateID);
    $update->execute();

    $update->close();
    $conn->close();

    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Delegate</title>
</head>
<body>

<h2>Edit Delegate</h2>

<form method="POST">
    <label>First Name:</label><br>
    <input type="text" name="FirstName"
           value="<?= htmlspecialchars($data['FirstName']) ?>" required><br><br>

    <label>Campus:</label><br>
    <input type="text" name="Campus"
           value="<?= htmlspecialchars($data['Campus']) ?>" required><br><br>

    <button type="submit">Update</button>
    <a href="index.php"><button type="button">Cancel</button></a>
</form>

</body>
</html>