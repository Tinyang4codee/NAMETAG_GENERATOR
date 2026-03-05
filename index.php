<!DOCTYPE html>
<html>
<head>
    <title>Delegate Admin</title>
    <style>
        table {
            border-collapse: collapse;
            width: 70%;
        }
        th, td {
            border: 1px solid #333;
            padding: 8px;
            text-align: center;
        }
        th {
            background: #f2f2f2;
        }
        button {
            padding: 6px 12px;
            margin: 3px;
        }
        .danger {
            background: red;
            color: white;
            border: none;
        }
    </style>
</head>
<body>
<?php
session_start();

if (isset($_POST['set_date'])) {
    $_SESSION['fellowship_date'] = $_POST['fellowship_date'];
}
?>

<h2>Set Fellowship Date</h2>

<form method="POST">
    <input type="date" name="fellowship_date" required
           value="<?= $_SESSION['fellowship_date'] ?? '' ?>">
    <button type="submit" name="set_date">Set Date</button>
</form>

<?php if (isset($_SESSION['fellowship_date'])): ?>
    <p><strong>Current Fellowship Date:</strong>
        <?= $_SESSION['fellowship_date'] ?>
    </p>
<?php endif; ?>

<hr>

<h2>Add Delegate</h2>

<form action="save.php" method="POST">
    <label>First Name:</label><br>
    <input type="text" name="FirstName" required><br><br>

    <label>Campus:</label><br>
    <input type="text" name="Campus" required><br><br>

    <button type="submit">Add Delegate</button>
</form>

<hr>

<h2>Delegate List</h2>

<?php
$conn = new mysqli("localhost", "root", "", "nametag");
$result = $conn->query("SELECT * FROM delage ORDER BY delegateID ASC");

$displayNumber = 1;
?>

<table>
    <tr>
        <th>No.</th>
        <th>Name</th>
        <th>Campus</th>
        <th>Actions</th>
    </tr>

    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $displayNumber++ ?></td>
        <td><?= htmlspecialchars($row['FirstName']) ?></td>
        <td><?= htmlspecialchars($row['Campus']) ?></td>
        <td>
            <a href="edit.php?delegateID=<?= $row['delegateID'] ?>">
                <button>Edit</button>
            </a>

            <a href="delete.php?delegateID=<?= $row['delegateID'] ?>"
               onclick="return confirm('Delete this delegate?');">
                <button class="danger">Delete</button>
            </a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<br>

<form action="delete_all.php" method="POST"
      onsubmit="return confirm('This will DELETE ALL delegates. Are you sure?');">
    <button type="submit" class="danger">
        Delete ALL Delegates
    </button>
</form>

<br><br>

<a href="generate.php?mode=preview">
    <button>Preview PDF</button>
</a>

<a href="generate.php">
    <button>Download PDF</button>
</a>

<a href="stats.php">
    <button>View Statistics</button>
</a>

<a href="add_to_stats.php"
   onclick="return confirm('Add this delegate to statistics?')">
   <button>Add to Statistics</button>
</a>

</body>
</html>