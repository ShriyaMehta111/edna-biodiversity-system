<?php
session_start();

// ======================
// Admin Auth Check
// ======================
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

require "db.php";

// ======================
// Search & Pagination
// ======================
$search = $_GET['search'] ?? '';
$limit = 10;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$whereClause = '';
$params = [];
if ($search !== '') {
    $whereClause = "WHERE CAST(ar.sample_id AS TEXT) ILIKE :search OR t.species ILIKE :search";
    $params[':search'] = "%$search%";
}

// Total rows
$countSql = "SELECT COUNT(*) FROM analysis_results ar LEFT JOIN taxonomy t ON ar.taxid = t.taxid $whereClause";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalRows = $countStmt->fetchColumn();
$totalPages = ceil($totalRows / $limit);

// Fetch results
$sql = "SELECT ar.result_id, ar.sample_id, ar.similarity_matched, ar.processed_at,
               t.species AS taxonomy_species
        FROM analysis_results ar
        LEFT JOIN taxonomy t ON ar.taxid = t.taxid
        $whereClause
        ORDER BY ar.result_id DESC
        LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Charts data
$speciesData = $pdo->query("
    SELECT t.species AS taxonomy_species, COUNT(*) AS cnt
    FROM analysis_results ar
    LEFT JOIN taxonomy t ON ar.taxid = t.taxid
    GROUP BY t.species
    ORDER BY cnt DESC
")->fetchAll(PDO::FETCH_ASSOC);

$speciesLabels = json_encode(array_map(fn($x)=>$x['taxonomy_species'] ?? 'Unknown', $speciesData));
$speciesCounts = json_encode(array_column($speciesData, 'cnt'));

// Similarity range for pie chart
$similarityData = $pdo->query("
    SELECT 
        CASE
            WHEN similarity_matched <= 50 THEN '0-50%'
            WHEN similarity_matched <= 80 THEN '51-80%'
            ELSE '81-100%'
        END AS range_group,
        COUNT(*) AS cnt
    FROM analysis_results
    GROUP BY range_group
")->fetchAll(PDO::FETCH_ASSOC);

$similarityLabels = json_encode(array_column($similarityData, 'range_group'));
$similarityCounts = json_encode(array_column($similarityData, 'cnt'));

include "header.php";
include "sidebar.php";
?>

<div class="main-content p-4">
    <h2 class="mb-4">📑 Analysis Results</h2>

    <!-- Search Form -->
    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search Sample ID or Species" value="<?= htmlspecialchars($search ?? '') ?>">
        </div>
        <div class="col-auto">
            <button class="btn btn-primary">🔍 Search</button>
        </div>
    </form>

    <!-- Table Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header fw-bold">Analysis Results Table</div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Result ID</th>
                        <th>Sample ID</th>
                        <th>Similarity Matched</th>
                        <th>Taxonomy Species</th>
                        <th>Processed At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rows): ?>
                        <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><?= $r['result_id'] ?></td>
                            <td><?= $r['sample_id'] ?></td>
                            <td><?= $r['similarity_matched'] ?>%</td>
                            <td><?= htmlspecialchars($r['taxonomy_species'] ?? '') ?></td>
                            <td><?= $r['processed_at'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">No results found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <nav>
        <ul class="pagination justify-content-center">
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>">Prev</a>
                </li>
            <?php endif; ?>
            <?php for ($i=1;$i<=$totalPages;$i++): ?>
                <li class="page-item <?= $i==$page?'active':'' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>">Next</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
    <?php endif; ?>

    <!-- Charts -->
    <div class="row mt-5">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm p-4">
                <h5 class="mb-3">Species Distribution</h5>
                <canvas id="speciesChart"></canvas>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm p-4">
                <h5 class="mb-3">Similarity Matched Distribution</h5>
                <canvas id="similarityChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Charts JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const speciesCtx = document.getElementById('speciesChart').getContext('2d');
new Chart(speciesCtx, {
    type: 'bar',
    data: {
        labels: <?= $speciesLabels ?>,
        datasets: [{
            label: 'Number of Results',
            data: <?= $speciesCounts ?>,
            backgroundColor: '#4AB19D'
        }]
    },
    options: { responsive:true, plugins:{ legend:{ display:false } } }
});

const similarityCtx = document.getElementById('similarityChart').getContext('2d');
new Chart(similarityCtx, {
    type: 'pie',
    data: {
        labels: <?= $similarityLabels ?>,
        datasets: [{
            data: <?= $similarityCounts ?>,
            backgroundColor: ['#FF6384','#36A2EB','#FFCE56']
        }]
    },
    options: { responsive:true }
});
</script>

<?php include "footer.php"; ?>
