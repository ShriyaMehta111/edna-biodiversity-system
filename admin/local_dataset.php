<?php
session_start();

/* ======================
   ADMIN AUTH CHECK
====================== */
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

require "db.php";

/* ======================
   ADD TAXONOMY
====================== */
if (isset($_POST['add_taxonomy'])) {
    $stmt = $pdo->prepare("
        INSERT INTO taxonomy
        (species, genus, family, order_name, class_name, phylum, kingdom)
        VALUES (:species, :genus, :family, :order_name, :class_name, :phylum, :kingdom)
    ");
    $stmt->execute([
        ':species'    => $_POST['species'],
        ':genus'      => $_POST['genus'],
        ':family'     => $_POST['family'],
        ':order_name' => $_POST['order_name'],
        ':class_name' => $_POST['class_name'],
        ':phylum'     => $_POST['phylum'],
        ':kingdom'    => $_POST['kingdom']
    ]);

    header("Location: local_dataset.php?msg=added");
    exit();
}

/* ======================
   UPDATE TAXONOMY
====================== */
if (isset($_POST['update_taxonomy'])) {
    $stmt = $pdo->prepare("
        UPDATE taxonomy SET
        species=:species, genus=:genus, family=:family,
        order_name=:order_name, class_name=:class_name,
        phylum=:phylum, kingdom=:kingdom
        WHERE taxid=:taxid
    ");
    $stmt->execute([
        ':species'    => $_POST['species'],
        ':genus'      => $_POST['genus'],
        ':family'     => $_POST['family'],
        ':order_name' => $_POST['order_name'],
        ':class_name' => $_POST['class_name'],
        ':phylum'     => $_POST['phylum'],
        ':kingdom'    => $_POST['kingdom'],
        ':taxid'      => $_POST['taxid']
    ]);

    header("Location: local_dataset.php?msg=updated");
    exit();
}

/* ======================
   DELETE TAXONOMY
====================== */
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM taxonomy WHERE taxid=:id");
    $stmt->execute([':id' => (int)$_GET['delete']]);

    header("Location: local_dataset.php?msg=deleted");
    exit();
}

/* ======================
   FETCH FOR EDIT
====================== */
$editRow = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM taxonomy WHERE taxid=:id");
    $stmt->execute([':id' => (int)$_GET['edit']]);
    $editRow = $stmt->fetch(PDO::FETCH_ASSOC);
}

/* ======================
   SEARCH BY TAXID
====================== */
$search_id = isset($_GET['search_id']) ? trim($_GET['search_id']) : '';

if ($search_id !== '') {
    $stmt = $pdo->prepare("SELECT * FROM taxonomy WHERE taxid = ?");
    $stmt->execute([(int)$search_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $rows = $pdo->query("SELECT * FROM taxonomy ORDER BY taxid DESC")->fetchAll(PDO::FETCH_ASSOC);
}

include "header.php";
include "sidebar.php";
?>

<div class="main-content">

    <h2 class="mb-4">📁 Local Dataset – Taxonomy Management</h2>

    <!-- STATUS MSG -->
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success">
            <?= ucfirst($_GET['msg']) ?> successfully.
        </div>
    <?php endif; ?>

    <!-- ======================
         SEARCH FORM
    ===================== -->
    <form class="mb-4 d-flex gap-2" method="GET">
        <input type="number" name="search_id" class="form-control w-25"
               placeholder="Search by Taxonomy ID"
               value="<?= htmlspecialchars($search_id) ?>">
        <button class="btn btn-primary">🔍 Search</button>
        <a href="local_dataset.php" class="btn btn-outline-secondary">Reset</a>
    </form>

    <!-- ======================
         ADD / EDIT FORM
    ===================== -->
    <div class="card shadow-sm mb-4">
        <div class="card-header fw-bold">
            <?= $editRow ? "✏️ Edit Taxonomy" : "➕ Add New Taxonomy" ?>
        </div>
        <div class="card-body">
            <form method="POST" class="row g-3">
                <?php if ($editRow): ?>
                    <input type="hidden" name="taxid" value="<?= $editRow['taxid'] ?>">
                <?php endif; ?>

                <?php
                $fields = ['species','genus','family','order_name','class_name','phylum','kingdom'];
                foreach ($fields as $f):
                ?>
                <div class="col-md-3">
                    <input type="text"
                           name="<?= $f ?>"
                           class="form-control"
                           placeholder="<?= ucfirst(str_replace('_',' ',$f)) ?>"
                           value="<?= $editRow[$f] ?? '' ?>"
                           required>
                </div>
                <?php endforeach; ?>

                <div class="col-12">
                    <button class="btn btn-success">
                        <?= $editRow ? "Update Taxonomy" : "Add Taxonomy" ?>
                    </button>
                    <?php if ($editRow): ?>
                        <a href="local_dataset.php" class="btn btn-secondary ms-2">Cancel</a>
                    <?php endif; ?>
                    <input type="hidden" name="<?= $editRow ? 'update_taxonomy' : 'add_taxonomy' ?>" value="1">
                </div>
            </form>
        </div>
    </div>

    <!-- ======================
         TAXONOMY TABLE
    ===================== -->
    <div class="card shadow-sm">
        <div class="card-header fw-bold">📋 Taxonomy Records</div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
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
                        <th width="160">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($rows): foreach ($rows as $r): ?>
                    <tr>
                        <td><?= $r['taxid'] ?></td>
                        <td><?= htmlspecialchars($r['species']) ?></td>
                        <td><?= htmlspecialchars($r['genus']) ?></td>
                        <td><?= htmlspecialchars($r['family']) ?></td>
                        <td><?= htmlspecialchars($r['order_name']) ?></td>
                        <td><?= htmlspecialchars($r['class_name']) ?></td>
                        <td><?= htmlspecialchars($r['phylum']) ?></td>
                        <td><?= htmlspecialchars($r['kingdom']) ?></td>
                        <td>
                            <a href="local_dataset.php?edit=<?= $r['taxid'] ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="local_dataset.php?delete=<?= $r['taxid'] ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Delete this taxonomy?')">
                               Delete
                            </a>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="9" class="text-center">No records found</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php include "footer.php"; ?>
