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
   DELETE TAXONOMY RECORD
============================= */
if (isset($_GET['delete'])) {
    $taxid = (int) $_GET['delete'];

    $stmt = $pdo->prepare("DELETE FROM taxonomy WHERE taxid = :id");
    $stmt->execute([':id' => $taxid]);

    header("Location: taxonomy.php?msg=deleted");
    exit();
}

/* =============================
   PAGINATION & SEARCH
============================= */
$limit  = 15;
$page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = trim($_GET['search'] ?? "");

/* =============================
   FETCH DATA
============================= */
if ($search !== "") {
    $stmt = $pdo->prepare("
        SELECT * FROM taxonomy
        WHERE species ILIKE :q
           OR genus ILIKE :q
           OR family ILIKE :q
           OR kingdom ILIKE :q
        ORDER BY taxid DESC
        LIMIT :limit OFFSET :offset
    ");

    $stmt->bindValue(':q', "%$search%", PDO::PARAM_STR);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $countStmt = $pdo->prepare("
        SELECT COUNT(*) FROM taxonomy
        WHERE species ILIKE :q
           OR genus ILIKE :q
           OR family ILIKE :q
           OR kingdom ILIKE :q
    ");
    $countStmt->bindValue(':q', "%$search%", PDO::PARAM_STR);
    $countStmt->execute();
    $totalRows = $countStmt->fetchColumn();

} else {
    $stmt = $pdo->prepare("
        SELECT * FROM taxonomy
        ORDER BY taxid DESC
        LIMIT :limit OFFSET :offset
    ");

    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $totalRows = $pdo->query("SELECT COUNT(*) FROM taxonomy")->fetchColumn();
}

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalPages = ceil($totalRows / $limit);

/* =============================
   LAYOUT
============================= */
include "header.php";
include "sidebar.php";
?>

<div class="main-content">

    <h2 class="mb-4">🌿 Taxonomy Management</h2>

    <!-- SEARCH -->
    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control"
                   placeholder="Search species, genus, family or kingdom"
                   value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-auto">
            <button class="btn btn-primary">🔍 Search</button>
        </div>
    </form>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
        <div class="alert alert-success">Taxonomy record deleted successfully.</div>
    <?php endif; ?>

    <!-- TABLE -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Species</th>
                            <th>Genus</th>
                            <th>Family</th>
                            <th>Order</th>
                            <th>Class</th>
                            <th>Phylum</th>
                            <th>Kingdom</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($rows): ?>
                        <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><?= $row['taxid'] ?></td>
                            <td><?= htmlspecialchars($row['species']) ?></td>
                            <td><?= htmlspecialchars($row['genus']) ?></td>
                            <td><?= htmlspecialchars($row['family']) ?></td>
                            <td><?= htmlspecialchars($row['order_name']) ?></td>
                            <td><?= htmlspecialchars($row['class_name']) ?></td>
                            <td><?= htmlspecialchars($row['phylum']) ?></td>
                            <td>
                                <span class="badge bg-success">
                                    <?= htmlspecialchars($row['kingdom']) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="taxonomy.php?delete=<?= $row['taxid'] ?>"
                                   onclick="return confirm('Delete this taxonomy record?')"
                                   class="btn btn-sm btn-outline-danger">
                                   🗑 Delete
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                No taxonomy records found.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- PAGINATION -->
    <?php if ($totalPages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link"
                           href="?page=<?= $i ?>&search=<?= urlencode($search) ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>

</div>

<?php include "footer.php"; ?>
