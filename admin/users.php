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
   ADD USER
============================= */
if (isset($_POST['add_user'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $institution = trim($_POST['institution']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, password, institution)
        VALUES (:name, :email, :password, :institution)
    ");
    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':password' => $password,
        ':institution' => $institution
    ]);

    header("Location: users.php?msg=added");
    exit();
}

/* =============================
   DELETE USER
============================= */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute([':id' => $id]);

    header("Location: users.php?msg=deleted");
    exit();
}

/* =============================
   FETCH USERS
============================= */
$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

/* =============================
   LAYOUT
============================= */
include "header.php";
include "sidebar.php";
?>

<div class="main-content">

    <h2 class="mb-4">👤 Manage Users</h2>

    <!-- SUCCESS MESSAGES -->
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success">
            <?php
                if ($_GET['msg'] === 'added') echo "User added successfully!";
                if ($_GET['msg'] === 'deleted') echo "User deleted successfully!";
            ?>
        </div>
    <?php endif; ?>

    <!-- ADD USER FORM -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="mb-3">Add New User</h5>

            <form method="POST" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="name" class="form-control" placeholder="Full Name" required>
                </div>
                <div class="col-md-3">
                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="institution" class="form-control" placeholder="Institution">
                </div>
                <div class="col-md-3">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>

                <div class="col-12">
                    <button type="submit" name="add_user" class="btn btn-primary">
                        ➕ Add User
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- USERS TABLE -->
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h5 class="mb-3">All Users</h5>

            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Institution</th>
                        <th width="120">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($users) > 0): ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?= $u['id'] ?></td>
                                <td><?= htmlspecialchars($u['name']) ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td><?= htmlspecialchars($u['institution']) ?></td>
                                <td>
                                    <a href="users.php?delete=<?= $u['id'] ?>"
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Delete this user?')">
                                       Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php include "footer.php"; ?>
