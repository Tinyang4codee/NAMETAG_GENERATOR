<?php
session_start();

$conn = new mysqli("localhost", "root", "", "nametag");
if ($conn->connect_error) {
    $_SESSION['msg'] = "❌ Database connection failed";
    header("Location: index.php");
    exit;
}

$sql = "
    INSERT INTO attendance_stats (Campus, fellowship_date, year, total_attendance)
    SELECT 
        Campus,
        fellowship_date,
        YEAR(fellowship_date),
        COUNT(*) AS total_attendance
    FROM delage
    WHERE fellowship_date IS NOT NULL
    GROUP BY Campus, fellowship_date
    ON DUPLICATE KEY UPDATE
        total_attendance = VALUES(total_attendance)
";

if ($conn->query($sql)) {
    $_SESSION['msg'] = "✅ Attendance statistics updated successfully";
} else {
    $_SESSION['msg'] = "❌ Error: " . $conn->error;
}

$conn->close();
header("Location: index.php");
exit;