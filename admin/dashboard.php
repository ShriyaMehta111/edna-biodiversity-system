<?php
session_start();

/* =============================
   ADMIN LOGIN CHECK
============================= */
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include "db.php";

/* =============================
   DASHBOARD STATS
============================= */
$total_samples  = $pdo->query("SELECT COUNT(*) FROM dna_samples")->fetchColumn();
$total_species  = $pdo->query("SELECT COUNT(DISTINCT species) FROM taxonomy")->fetchColumn();
$total_users    = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_kingdoms = $pdo->query("SELECT COUNT(DISTINCT kingdom) FROM taxonomy")->fetchColumn();

/* =============================
   CHART DATA
============================= */
$stmt = $pdo->query("
    SELECT kingdom, COUNT(*) AS count
    FROM taxonomy
    WHERE kingdom IS NOT NULL
    GROUP BY kingdom
    ORDER BY kingdom
");
$chartData = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labels = json_encode(array_column($chartData, 'kingdom'));
$values = json_encode(array_column($chartData, 'count'));

/* =============================
   LAYOUT
============================= */
include "header.php";
include "sidebar.php";
?>

<div class="main-content">

    <h2 class="mb-4">📊 Dashboard Overview</h2>

    <!-- STATS CARDS -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center p-4">
                <h3 class="fw-bold text-primary"><?= $total_samples ?></h3>
                <p class="text-muted mb-0">DNA Samples</p>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center p-4">
                <h3 class="fw-bold text-success"><?= $total_species ?></h3>
                <p class="text-muted mb-0">Unique Species</p>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center p-4">
                <h3 class="fw-bold text-warning"><?= $total_users ?></h3>
                <p class="text-muted mb-0">Registered Users</p>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center p-4">
                <h3 class="fw-bold text-danger"><?= $total_kingdoms ?></h3>
                <p class="text-muted mb-0">Kingdoms</p>
            </div>
        </div>
    </div>

    <!-- CHART -->
    <div class="card shadow-sm border-0 p-4">
        <h5 class="mb-3">🌿 Species Distribution by Kingdom</h5>
        <canvas id="kingdomChart" height="120"></canvas>
    </div>

</div>

<!-- CHART JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('kingdomChart');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= $labels ?>,
        datasets: [{
            label: 'Species Count',
            data: <?= $values ?>,
            backgroundColor: '#4f6df5'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>

<?php include "footer.php"; ?>
