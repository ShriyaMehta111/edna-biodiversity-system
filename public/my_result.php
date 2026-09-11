<?php
session_start();
include 'db.php';

// Ensure user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$user_id  = $_SESSION['user_id'];
$username = $_SESSION['username'];
$email    = $_SESSION['email'];

try {
    // Fetch samples (DESC for table)
    $stmt = $pdo->prepare("
        SELECT ds.sample_id, ds.upload_type, ds.uploaded_at,
               ar.similarity_matched, ar.processed_at,
               t.species, t.kingdom
        FROM dna_samples ds
        LEFT JOIN analysis_results ar ON ds.sample_id = ar.sample_id
        LEFT JOIN taxonomy t ON ar.taxid = t.taxid
        WHERE ds.user_id = :user_id
        ORDER BY ds.uploaded_at DESC
    ");
    $stmt->execute(['user_id' => $user_id]);
    $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Summary metrics
    $total_samples = count($samples);
    $species_detected = 0;
    $total_similarity = 0;
    $kingdoms = [];

    foreach ($samples as $s) {
        if (!empty($s['species'])) $species_detected++;
        if (!empty($s['similarity_matched'])) $total_similarity += $s['similarity_matched'];
        if (!empty($s['kingdom'])) $kingdoms[] = $s['kingdom'];
    }

    $average_similarity = $total_samples ? round($total_similarity / $total_samples, 2) : 0;
    $unique_kingdoms = count(array_unique($kingdoms));
    $kingdom_counts = array_count_values($kingdoms);

    /* ============================
       CHART DATA (ASC order)
       ============================ */

    // Copy samples and sort by upload time ASC
    $chart_samples = $samples;
    usort($chart_samples, function ($a, $b) {
        return strtotime($a['uploaded_at']) <=> strtotime($b['uploaded_at']);
    });

    $sample_labels = [];
    $similarity_data = [];

    foreach ($chart_samples as $i => $s) {
        $sample_labels[] = "Sample " . ($i + 1); // TRUE upload order
        $similarity_data[] = !empty($s['similarity_matched']) ? $s['similarity_matched'] : 0;
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Results | EDNA Biodiversity</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body {
    background-color: #f6f8fa;
    font-family: 'Segoe UI', sans-serif;
}
.summary-card {
    border-radius: 12px;
}
.table th, .table td {
    vertical-align: middle;
}

@media print {
    nav, .no-print, canvas {
        display: none !important;
    }
    body {
        background: #fff;
    }
    .card {
        box-shadow: none !important;
        border: 1px solid #ccc;
    }
}
</style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm no-print">
<div class="container">
    <a class="navbar-brand" href="index.php">EDNA Biodiversity</a>
    <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link active" href="my_result.php">My Results</a></li>
        <li class="nav-item"><a class="nav-link" href="upload.php">Upload</a></li>
        <li class="nav-item"><a class="nav-link text-warning" href="logout.php">Logout</a></li>
    </ul>
</div>
</nav>

<div class="container my-5">

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-1">DNA Analysis Report</h3>
        <small class="text-muted">
            User: <?= htmlspecialchars($username) ?> |
            Email: <?= htmlspecialchars($email) ?> |
            Date: <?= date('d-m-Y') ?>
        </small>
    </div>
    <button onclick="window.print()" class="btn btn-success no-print">📄 Save as PDF</button>
</div>

<!-- SUMMARY -->
<div class="row text-center g-4 mb-4">
    <div class="col-md-3"><div class="card p-4 summary-card"><h4><?= $total_samples ?></h4><p>Uploaded Samples</p></div></div>
    <div class="col-md-3"><div class="card p-4 summary-card"><h4><?= $species_detected ?></h4><p>Species Detected</p></div></div>
    <div class="col-md-3"><div class="card p-4 summary-card"><h4><?= $average_similarity ?>%</h4><p>Average Similarity</p></div></div>
    <div class="col-md-3"><div class="card p-4 summary-card"><h4><?= $unique_kingdoms ?></h4><p>Unique Kingdoms</p></div></div>
</div>

<!-- CHARTS -->
<div class="row mb-4 no-print">
    <div class="col-md-6">
        <div class="card p-4">
            <h6 class="text-center">Similarity Percentage per Upload Order</h6>
            <canvas id="similarityChart"></canvas>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card p-4">
            <h6 class="text-center">Kingdom Distribution</h6>
            <canvas id="kingdomChart"></canvas>
        </div>
    </div>
</div>

<!-- TABLE -->
<div class="card p-4">
<h5 class="mb-3">Uploaded DNA Samples</h5>
<div class="table-responsive">
<table class="table table-bordered table-hover">
<thead class="table-success">
<tr>
<th>#</th><th>Sample ID</th><th>Upload Type</th><th>Uploaded At</th>
<th>Species</th><th>Kingdom</th><th>Similarity (%)</th><th>Processed At</th>
</tr>
</thead>
<tbody>
<?php if ($samples): foreach ($samples as $i => $s): ?>
<tr>
<td><?= $i+1 ?></td>
<td><?= htmlspecialchars($s['sample_id']) ?></td>
<td><?= htmlspecialchars($s['upload_type']) ?></td>
<td><?= htmlspecialchars($s['uploaded_at']) ?></td>
<td><?= htmlspecialchars($s['species'] ?? '-') ?></td>
<td><?= htmlspecialchars($s['kingdom'] ?? '-') ?></td>
<td><?= htmlspecialchars($s['similarity_matched'] ?? '-') ?></td>
<td><?= htmlspecialchars($s['processed_at'] ?? '-') ?></td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="8" class="text-center">No samples uploaded yet.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>

</div>

<script>
new Chart(document.getElementById('similarityChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($sample_labels) ?>,
        datasets: [{
            label: 'Similarity %',
            data: <?= json_encode($similarity_data) ?>,
            tension: 0.3,
            fill: false
        }]
    },
    options: {
        scales: {
            x: {
                title: { display: true, text: 'Upload Order (First → Last)' }
            },
            y: {
                min: 0,
                max: 100,
                title: { display: true, text: 'Similarity %' }
            }
        }
    }
});

new Chart(document.getElementById('kingdomChart'), {
    type: 'pie',
    data: {
        labels: <?= json_encode(array_keys($kingdom_counts)) ?>,
        datasets: [{
            data: <?= json_encode(array_values($kingdom_counts)) ?>
        }]
    }
});
</script>

</body>
</html>
