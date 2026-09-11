<?php
session_start();
require 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $institution = trim($_POST['institution']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $message = "Passwords do not match!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            $sql = "INSERT INTO users (name, email, institution, password) VALUES (:name, :email, :institution, :password)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':institution' => $institution,
                ':password' => $hashed_password
            ]);
            $message = "Registration successful! You can now log in.";
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | EDNA Biodiversity</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .password-toggle {
            cursor: pointer;
            position: absolute;
            right: 10px;
            top: 36px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white text-center">
                        <h4>Create Your Research Account</h4>
                        <small>Access EDNA Biodiversity Data & Tools</small>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-info"><?= $message ?></div>
                        <?php endif; ?>

                        <form action="register.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" placeholder="e.g. Dr. John Doe" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" placeholder="e.g. johndoe@domain.com" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Institution / Organization</label>
                                <input type="text" name="institution" class="form-control" placeholder="e.g. National Biodiversity Institute" required>
                            </div>

                            <div class="mb-3 position-relative">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" id="password" class="form-control" required>
                                <span class="password-toggle" onclick="togglePassword('password')">👁️</span>
                            </div>

                            <div class="mb-3 position-relative">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                                <span class="password-toggle" onclick="togglePassword('confirm_password')">👁️</span>
                            </div>

                            <button class="btn btn-success w-100">Register</button>
                        </form>

                        <p class="text-center mt-3">
                            Already have an account? <a href="login.php" class="text-success">Login here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>
