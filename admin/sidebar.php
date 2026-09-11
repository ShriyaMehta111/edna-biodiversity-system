<?php
// sidebar.php
// Safe to include anywhere
$current = basename($_SERVER['PHP_SELF']);
?>

<style>
.sidebar {
    width: 260px;
    background: var(--dark-bg);
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    color: #fff;
    padding-top: 22px;
    box-shadow: 2px 0 12px rgba(0,0,0,0.2);
    z-index: 1000;
}

.sidebar h2 {
    text-align: center;
    font-size: 20px;
    margin-bottom: 25px;
    font-weight: 700;
}

.sidebar ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar li {
    margin-bottom: 4px;
}

.sidebar a {
    display: block;
    padding: 12px 22px;
    color: #dcdcdc;
    text-decoration: none;
    font-size: 15px;
    font-weight: 600;
    transition: all 0.25s ease;
}

.sidebar a:hover {
    background: var(--dark-hover);
    color: #fff;
}

.sidebar a.active {
    background: var(--primary);
    color: #fff;
    border-radius: 6px;
    margin: 0 10px;
}

.sidebar .footer {
    position: absolute;
    bottom: 15px;
    width: 100%;
    text-align: center;
    font-size: 12px;
    color: #aaa;
}
</style>

<div class="sidebar">
    <h2>EDNA Admin</h2>

    <ul>
        <li><a href="dashboard.php" class="<?= $current=='dashboard.php'?'active':'' ?>">📊 Dashboard</a></li>
        <li><a href="users.php" class="<?= $current=='users.php'?'active':'' ?>">👤 Manage Users</a></li>
        <li><a href="samples.php" class="<?= $current=='samples.php'?'active':'' ?>">🧬 DNA Samples</a></li>
        <li><a href="taxonomy.php" class="<?= $current=='taxonomy.php'?'active':'' ?>">🌿 Taxonomy</a></li>
        <li><a href="local_dataset.php" class="<?= $current=='local_dataset.php'?'active':'' ?>">📁 Local Dataset</a></li>
        <li><a href="analysis_results.php" class="<?= $current=='analysis_results.php'?'active':'' ?>">📑 Analysis Results</a></li>
        <li><a href="logout.php">🔒 Logout</a></li>
    </ul>

    <div class="footer">EDNA System © <?= date('Y') ?></div>
</div>
