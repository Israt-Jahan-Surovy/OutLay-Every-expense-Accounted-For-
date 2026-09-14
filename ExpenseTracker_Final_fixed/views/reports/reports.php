<?php
session_start();
require_once __DIR__. '/../../models/expenseModel.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';

// Fetch overall system data
$expenses = getSystemExpensesByCategory($from_date, $to_date);

$grandTotal = array_sum(array_column($expenses, 'total_amount'));

$chartLabels = [];
$chartData = [];
$legendList = [];

if ($grandTotal > 0) {
    foreach ($expenses as $item) {
        $percentage = round(($item['total_amount'] / $grandTotal) * 100);
        $chartLabels[] = $item['category_name'];
        $chartData[] = $item['total_amount'];
        $legendList[] = [
            'name' => $item['category_name'],
            'percentage' => $percentage
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Expense Tracker</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/reports.css">
    <script src="../js/expense.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

    <?php include '../includes/sidebar.php'; ?>

    <main class="main-content">
        <h1 class="page-title">Reports</h1>

        <form method="GET" action="reports.php" class="report-filter-form">
            <input type="date" name="from_date" value="<?php echo htmlspecialchars($from_date); ?>" class="date-input">
            <input type="date" name="to_date" value="<?php echo htmlspecialchars($to_date); ?>" class="date-input">
            <button type="submit" class="btn-generate">Generate</button>
        </form>

        <div class="report-display-container">
            <div class="chart-wrapper">
                <?php if ($grandTotal > 0): ?>
                    <canvas id="expensesPieChart"></canvas>
                <?php else: ?>
                    <div class="placeholder-circle">
                        <span>Generated Report pie chart will be here</span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="legend-wrapper">
                <h3>Expenses by category:</h3>
                <?php if (!empty($legendList)): ?>
                    <ul class="category-legend">
                        <?php foreach ($legendList as $legend): ?>
                            <li>
                                <span class="cat-name"><?php echo htmlspecialchars($legend['name']); ?></span>
                                <span class="cat-percent"><?php echo $legend['percentage']; ?>%</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="no-data">No expense data available for this range.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php if ($grandTotal > 0): ?>
    <script>
        const ctx = document.getElementById('expensesPieChart').getContext('2d');
       new Chart(ctx, {
    type: 'pie',
    data: {
        labels: <?php echo json_encode($chartLabels); ?>,
        datasets: [{
            data: <?php echo json_encode($chartData); ?>,
            backgroundColor: ['#4a90e2', '#e5c158', '#50e3c2', '#b8e986', '#bd10e0'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false, // Prevents full-screen expansion
        plugins: {
            legend: { display: false } // Hide built-in chart legend
        }
    }
});
    </script>
    <?php endif; ?>

</body>
</html>