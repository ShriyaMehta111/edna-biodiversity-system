<?php
session_start();

/* =============================
   ADMIN AUTH CHECK
============================= */
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include "db.php";

/* =============================
   FETCH DNA SAMPLES
============================= */
$stmt = $pdo->query("
    SELECT 
        s.sample_id,
        s.sequence,
        s.upload_type,
        s.uploaded_at,
        u.name AS user_name
    FROM dna_samples s
    LEFT JOIN users u ON s.user_id = u.id
    ORDER BY s.sample_id DESC
");

$samples = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =============================
   LAYOUT
============================= */
include "header.php";
include "sidebar.php";
?>

<div class="main-content">

    <h2 class="mb-4">🧬 DNA Samples</h2>

    <div class="card shadow-sm border-0">
        <div class="card-body">

            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Sequence</th>
                        <th>Upload Type</th>
                        <th>Uploaded At</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($samples) > 0): ?>
                    <?php foreach ($samples as $row): ?>
                        <tr>
                            <td><?= $row['sample_id'] ?></td>
                            <td><?= htmlspecialchars($row['user_name'] ?? 'Unknown') ?></td>
                            <td>
                                <div class="sequence-box">
                                    <?= htmlspecialchars($row['sequence']) ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    <?= htmlspecialchars($row['upload_type']) ?>
                                </span>
                            </td>
                            <td><?= $row['uploaded_at'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            No DNA samples found.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>

        </div>
    </div>

</div>

<style>
/* Only page-specific styling */
.sequence-box {
    max-width: 420px;
    max-height: 90px;
    overflow-y: auto;
    font-family: monospace;
    font-size: 13px;
    background: #f8f9fa;
    border-radius: 6px;
    padding: 8px;
    border: 1px solid #e1e1e1;
}
</style>

<?php include "footer.php"; ?>
