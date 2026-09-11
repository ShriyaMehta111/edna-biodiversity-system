<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $sql = "
    SELECT ar.result_id, ar.sample_id, ar.similarity_matched, ar.processed_at,
           ds.sequence,
           t.taxid, t.scientific_name, t.species, t.genus, t.family, t.order_name,
           t.class_name, t.phylum, t.kingdom
    FROM analysis_results ar
    JOIN dna_samples ds ON ar.sample_id = ds.sample_id
    LEFT JOIN taxonomy t ON ar.taxid = t.taxid
    WHERE ds.user_id = :uid
    ORDER BY ar.result_id DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':uid' => $user_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>DNA Analysis Results | eDNA Biodiversity</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background:#f8f9fa; }
        .container-box { margin-top:50px; background:white; padding:30px; border-radius:10px; box-shadow:0 4px 20px rgba(0,0,0,0.1); }
        table th, table td { vertical-align:middle !important; }
        .badge-local { background-color:#198754; color:#fff; padding:.35rem .6rem; border-radius:.35rem; }
        .badge-ncbi { background-color:#0d6efd; color:#fff; padding:.35rem .6rem; border-radius:.35rem; }
        .badge-unknown { background-color:#6c757d; color:#fff; padding:.35rem .6rem; border-radius:.35rem; }
        textarea { resize:none; }
    </style>
</head>
<body>
<div class="container container-box">
    <h2 class="text-center mb-4">🔬<a href="my_result.php"> DNA Analysis Results</a></h2>
    <a href="upload.php" class="btn btn-primary mb-3">⬅ Upload New Sequence</a>

    <?php if (empty($rows)): ?>
        <div class="alert alert-info">No analysis results found. Please upload a DNA sequence.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark text-center">
                    <tr>
                        <th>#</th>
                        <th>Sequence</th>
                        
                        <th>Species</th>
                        <th>Genus</th>
                        <th>Family</th>
                        <th>Order</th>
                        <th>Class</th>
                        <th>Phylum</th>
                        <th>Kingdom</th>
                        <th>Similarity (%)</th>
                        <th>Processed At</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $i => $r): ?>
                    <tr class="text-center">
                        <td><?= $i + 1 ?></td>
                        <td style="max-width:200px;"><textarea class="form-control" rows="2" readonly><?= htmlspecialchars($r['sequence']) ?></textarea></td>
                        
                        <td><?= htmlspecialchars($r['species'] ?? '') ?></td>
                        <td><?= htmlspecialchars($r['genus'] ?? '') ?></td>
                        <td><?= htmlspecialchars($r['family'] ?? '') ?></td>
                        <td><?= htmlspecialchars($r['order_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($r['class_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($r['phylum'] ?? '') ?></td>
                        <td><?= htmlspecialchars($r['kingdom'] ?? '') ?></td>
                        <td><?= number_format((float)$r['similarity_matched'], 2) ?></td>
                        <td><?= htmlspecialchars($r['processed_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
