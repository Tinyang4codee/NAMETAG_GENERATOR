<?php
$conn = new mysqli("localhost", "root", "", "nametag");
if ($conn->connect_error) {
    die("Database connection failed");
}

$currentYear = date('Y');
$selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : $currentYear;
$selectedDate = $_GET['date'] ?? '';

/* ======================
GET AVAILABLE YEARS
====================== */
$yearList = [];
$res = $conn->query("SELECT DISTINCT year FROM attendance_stats ORDER BY year DESC");
while ($row = $res->fetch_assoc()) {
    $yearList[] = $row['year'];
}
if (empty($yearList)) $yearList[] = $currentYear;

/* ======================
GET AVAILABLE DATES (BASED ON YEAR)
====================== */
$dateList = [];
$stmt = $conn->prepare("
    SELECT DISTINCT fellowship_date
    FROM attendance_stats
    WHERE year = ?
    ORDER BY fellowship_date DESC
");
$stmt->bind_param("i", $selectedYear);
$stmt->execute();
$stmt->bind_result($date);
while ($stmt->fetch()) {
    $dateList[] = $date;
}
$stmt->close();

/* ======================
WHERE CLAUSE BUILDER
====================== */
$where = "year = ?";
$params = [$selectedYear];
$types = "i";

if (!empty($selectedDate)) {
    $where .= " AND fellowship_date = ?";
    $params[] = $selectedDate;
    $types .= "s";
}

/* ======================
TOTAL ATTENDANCE
====================== */
$yearTotal = 0;
$stmt = $conn->prepare("
    SELECT COALESCE(SUM(total_attendance), 0)
    FROM attendance_stats
    WHERE $where
");
$stmt->bind_param($types, ...$params);
$stmt->execute();
$stmt->bind_result($yearTotal);
$stmt->fetch();
$stmt->close();

/* ======================
ATTENDANCE PER CAMPUS
====================== */
$campusLabels = [];
$campusTotals = [];
$stmt = $conn->prepare("
    SELECT campus, COALESCE(SUM(total_attendance), 0)
    FROM attendance_stats
    WHERE $where
    GROUP BY campus
    ORDER BY campus
");
$stmt->bind_param($types, ...$params);
$stmt->execute();
$stmt->bind_result($campus, $total);
while ($stmt->fetch()) {
    $campusLabels[] = $campus;
    $campusTotals[] = $total;
}
$stmt->close();

/* ======================
TREND PER DATE (YEAR VIEW ONLY)
====================== */
$trendDates = [];
$trendTotals = [];
if (empty($selectedDate)) {
    $stmt = $conn->prepare("
        SELECT fellowship_date, COALESCE(SUM(total_attendance), 0)
        FROM attendance_stats
        WHERE year = ?
        GROUP BY fellowship_date
        ORDER BY fellowship_date
    ");
    $stmt->bind_param("i", $selectedYear);
    $stmt->execute();
    $stmt->bind_result($d, $t);
    while ($stmt->fetch()) {
        $trendDates[] = $d;
        $trendTotals[] = $t;
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
<title>Attendance Statistics</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body { font-family: Arial, sans-serif; padding: 20px; }
.chart-box { max-width: 700px; margin: 40px auto; }
select { padding: 6px; margin: 0 5px; }
table { border-collapse: collapse; width: 60%; margin: 20px auto; }
th, td { border: 1px solid #999; padding: 8px; text-align: center; }
th { background: #f2f2f2; }
h2, h3, p { text-align: center; }
</style>
</head>

<body>

<h2>📊 Attendance Statistics Dashboard</h2>

<form method="get" style="text-align:center;">
    <label><strong>Year:</strong></label>
    <select name="year" onchange="this.form.submit()">
        <?php foreach ($yearList as $yr): ?>
            <option value="<?= $yr ?>" <?= $yr == $selectedYear ? 'selected' : '' ?>><?= $yr ?></option>
        <?php endforeach; ?>
    </select>

    <label><strong>Date:</strong></label>
    <select name="date" onchange="this.form.submit()">
        <option value="">All Dates</option>
        <?php foreach ($dateList as $d): ?>
            <option value="<?= $d ?>" <?= $d === $selectedDate ? 'selected' : '' ?>><?= $d ?></option>
        <?php endforeach; ?>
    </select>
</form>

<h3>
<?= empty($selectedDate)
    ? "Total Attendance for $selectedYear"
    : "Attendance on " . htmlspecialchars($selectedDate) ?>
</h3>

<p><strong><?= $yearTotal ?></strong> people attended</p>

<table>
<tr><th>Campus</th><th>Total Attendees</th></tr>
<?php foreach ($campusLabels as $i => $campus): ?>
<tr>
    <td><?= htmlspecialchars($campus) ?></td>
    <td><?= $campusTotals[$i] ?></td>
</tr>
<?php endforeach; ?>
</table>

<div class="chart-box">
    <canvas id="barChart"></canvas>
</div>

<div class="chart-box">
    <canvas id="pieChart"></canvas>
</div>

<?php if (empty($selectedDate)): ?>
<div class="chart-box">
    <canvas id="lineChart"></canvas>
</div>
<?php endif; ?>

<script>
/* BAR CHART */
new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($campusLabels) ?>,
        datasets: [{
            label: 'Attendance',
            data: <?= json_encode($campusTotals) ?>,
            backgroundColor: '#4e73df'
        }]
    },
    options: {
        scales: {
            y: { beginAtZero: true, ticks: { precision: 0 } }
        }
    }
});

/* PIE CHART */
new Chart(document.getElementById('pieChart'), {
    type: 'pie',
    data: {
        labels: <?= json_encode($campusLabels) ?>,
        datasets: [{
            data: <?= json_encode($campusTotals) ?>,
            backgroundColor: ['#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796']
        }]
    }
});

<?php if (empty($selectedDate)): ?>
/* LINE CHART */
new Chart(document.getElementById('lineChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($trendDates) ?>,
        datasets: [{
            label: 'Attendance Trend',
            data: <?= json_encode($trendTotals) ?>,
            borderColor: '#2e59d9',
            tension: 0.3,
            fill: false
        }]
    },
    options: {
        scales: {
            y: { beginAtZero: true, ticks: { precision: 0 } }
        }
    }
});
<?php endif; ?>
</script>
<a href="index  .php">
    <button>Back</button>
</a>
</body>
</html>