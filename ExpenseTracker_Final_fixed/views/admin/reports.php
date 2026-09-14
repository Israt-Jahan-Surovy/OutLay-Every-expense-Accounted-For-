<?php
require_once __DIR__ . '/_admin_ui.php';
require_once __DIR__ . '/../../models/expenseModel.php';
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';
$dateError = '';
if ($from_date !== '' && $to_date !== '' && $from_date > $to_date) {
    $dateError = '"From" date must be on or before the "To" date.';
    $expenses = [];
} else {
    $expenses = getSystemExpensesByCategory($from_date, $to_date);
}
$grandTotal = array_sum(array_column($expenses, 'total_amount'));
$chartLabels = []; $chartData = []; $legendList = [];
if ($grandTotal > 0) {
    foreach ($expenses as $item) {
        $percentage = round(($item['total_amount'] / $grandTotal) * 100);
        $chartLabels[] = $item['category_name'];
        $chartData[] = $item['total_amount'];
        $legendList[] = ['name' => $item['category_name'], 'percentage' => $percentage];
    }
}
adminPageStart('Reports','reports');
?>
<section class="page-head"><div><h1>Reports</h1></div></section>
<form class="filterbar" method="get">
<label class="filter-field"><span>From</span><input type="date" name="from_date" value="<?=ae($from_date)?>"></label>
<label class="filter-field"><span>To</span><input type="date" name="to_date" value="<?=ae($to_date)?>"></label>
<button class="btn btn-gold">Generate</button>
</form>
<?php if ($dateError): ?><p style="color:#e2696d;margin:-10px 0 20px"><?=ae($dateError)?></p><?php endif; ?>
<div class="report-display">
<div class="chart-wrapper">
<?php if ($grandTotal > 0): ?><canvas id="expensesPieChart"></canvas>
<?php else: ?><div class="placeholder-circle"><span>Generated Report pie chart will be here</span></div><?php endif; ?>
</div>
<div class="legend-wrapper"><h3>Expenses by category:</h3>
<?php if (!empty($legendList)): ?><ul class="category-legend"><?php foreach ($legendList as $legend): ?><li><span><?=ae($legend['name'])?></span><span><?=$legend['percentage']?>%</span></li><?php endforeach; ?></ul>
<?php else: ?><p style="color:var(--muted)">No expense data available for this range.</p><?php endif; ?>
</div>
</div>
<?php if ($grandTotal > 0): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('expensesPieChart').getContext('2d');
new Chart(ctx, {
    type: 'pie',
    data: {
        labels: <?php echo json_encode($chartLabels); ?>,
        datasets: [{ data: <?php echo json_encode($chartData); ?>, backgroundColor: ['#e8c17e','#5b8fd6','#3fae76','#e2696d','#c9c8c2'], borderWidth: 0 }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
});
</script>
<?php endif; ?>
<?php adminPageEnd(); ?>
