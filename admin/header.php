<?php
// header.php
// NO session_start
// NO redirect
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>EDNA Admin Panel</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
:root {
    --dark-bg: #1e1e2d;
    --dark-hover: #2f3240;
    --primary: #4f6df5;
    --light-bg: #f4f6fb;
}

body {
    margin: 0;
    font-family: "Segoe UI", Arial, sans-serif;
    background: var(--light-bg);
}

/* Main content area */
.main-content {
    margin-left: 260px;
    padding: 25px;
}
</style>
</head>
<body>
