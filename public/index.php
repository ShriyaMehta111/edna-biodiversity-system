<?php
session_start();
include "db.php";
$user_count = 0;
$species_count = 0;

try {
    // Count total users
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM users");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $user_count = $row['total'];

    // Count total species (taxonomy table)
    $stmt2 = $pdo->query("SELECT COUNT(DISTINCT species) AS total FROM taxonomy");
    $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
    $species_count = $row2['total'];

} catch (PDOException $e) {
    echo "Query error: " . $e->getMessage();
}


// read logged-in user values (if set)
$username = $_SESSION['username'] ?? null;
$email = $_SESSION['email'] ?? null;



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>EDNA Biodiversity</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS embedded -->
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f6f8fa;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .navbar-brand img {
            height: 40px;
            width: auto;
        }

        header {
            background: url('images/hero-bg.jpg') no-repeat center center;
            background-size: cover;
            color: #fff;
        }

        header h1, header p {
            color: black;
	
           # text-shadow: 1px 1px 4px rgba(0,0,0,0.6);
        }

        .card-title {
            font-weight: bold;
        }

        footer {
            background-color: #1b5e20;
        }

        section h2 {
            margin-bottom: 30px;
            font-weight: bold;
        }

        ul {
            padding-left: 20px;
        }

        ul li {
            margin-bottom: 8px;
        }

        .btn-success {
            background-color: #2e7d32;
            border-color: #2e7d32;
        }

        .btn-success:hover {
            background-color: #1b5e20;
            border-color: #1b5e20;
        }

        .carousel-item img {
            width: 100%;
            height: 600px; /* adjust height as needed */
            object-fit: cover; /* ensures the image covers entire space */
        }

        .carousel-caption {
            background-color: rgba(0, 0, 0, 0.40);
            padding: 15px;
            border-radius: 5px;
        }

        .user-dropdown .dropdown-item-text {
            white-space: normal;
            color: #6c757d;
        }

	.welcome,.lead{
	   color:black;
	}

        @media (max-width: 576px) {
            .carousel-item img {
                height: 320px;
            }
        }
    </style>
</head>
<body>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <!-- if you have a logo put it in images/logo.png -->
              <img src="images/logo3.png" alt="Logo"
     style="height:60px; width:80px; margin-right:-20px; margin-left:-25px;">
                EDNA Biodiversity
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link <?= (!isset($_GET['view']) || $_GET['view'] === 'home') ? 'active' : '' ?>" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link <?= (isset($_GET['view']) && $_GET['view']==='upload') ? 'active' : '' ?>" href="upload.php">Upload</a></li>
                    <li class="nav-item"><a class="nav-link <?= (isset($_GET['view']) && $_GET['view']==='my_result') ? 'active' : '' ?>" href="my_result.php">My Result</a></li>

                    <?php if ($username): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle" style="font-size:1.4rem;"></i>
                                <span class="ms-2"><?= htmlspecialchars($username) ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end user-dropdown" aria-labelledby="userMenu">
                                <li class="dropdown-item-text px-3 py-2"><strong><?= htmlspecialchars($username) ?></strong><br><small class="text-muted"><?= htmlspecialchars($email) ?></small></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link <?= (isset($_GET['view']) && $_GET['view']==='login') ? 'active' : '' ?>" href="login.php">Login</a></li>
                        <li class="nav-item"><a class="nav-link <?= (isset($_GET['view']) && $_GET['view']==='register') ? 'active' : '' ?>" href="register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Carousel -->
    <div id="ednaCarousel" class="carousel slide" data-bs-ride="carousel">
      <div class="carousel-indicators">
        <button type="button" data-bs-target="#ednaCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
        <button type="button" data-bs-target="#ednaCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
        <button type="button" data-bs-target="#ednaCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
      </div>

      <div class="carousel-inner">
        <div class="carousel-item active">
          <img src="images/c1.jpeg" class="d-block w-100" alt="Slide 1">
          <div class="carousel-caption d-none d-md-block">
            <h5>Explore DNA Samples</h5>
            <p>Discover biodiversity through environmental DNA.</p>
          </div>
        </div>
        <div class="carousel-item">
          <img src="images/c2.jpg" class="d-block w-100" alt="Slide 2">
          <div class="carousel-caption d-none d-md-block">
            <h5>Taxonomy Explorer</h5>
            <p>Study species relationships and classifications.</p>
          </div>
        </div>
        <div class="carousel-item">
          <img src="images/c4.jpg" class="d-block w-100" alt="Slide 3">
          <div class="carousel-caption d-none d-md-block">
            <h5>Research & Conservation</h5>
            <p>Use DNA data to support environmental research.</p>
          </div>
        </div>
      </div>

      <button class="carousel-control-prev" type="button" data-bs-target="#ednaCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
      </button>

      <button class="carousel-control-next" type="button" data-bs-target="#ednaCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
      </button>
    </div>

    <!-- Hero Section -->
    <header class="bg-light py-5">
        <div class="container text-center">
            <h1 class="display-4  welcome">Welcome to EDNA Biodiversity</h1>
            <p class="lead">Explore DNA-based biodiversity data and contribute to conservation research.</p>
            <a href="upload.php" class="btn btn-success btn-lg mt-3">Upload DNA Sample</a>
        </div>
    </header>

    <!-- About Section -->
    <section class="py-5 bg-white">
        <div class="container">
            <div class="row align-items-center">
		 <h2 class="about"><center>About EDNA Biodiversity</center></h2>
                <div class="col-md-6">
                   
                    <p>EDNA Biodiversity is a platform for managing environmental DNA (eDNA) data. 
                    You can upload DNA samples, explore species, and study taxonomy to understand biodiversity patterns.</p>
                    <ul>
                        <li>Store and manage DNA samples securely</li>
                        <li>View and analyze taxonomy data</li>
                        <li>Generate insights for research and conservation</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <img src="images/edna.jpg" alt="eDNA" class="img-fluid rounded shadow">
		    <img src="images/edna3.jpeg" alt="eDNA" style="height:250px; width:280px; margin-left:17px; class="img-fluid rounded shadow">

                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5 keyfet">Key Features</h2>
        <div class="row text-center">

            <!-- Feature 1 -->
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">

                        <!-- Round Icon -->
                        <img src="images/dna_sample.png"
                             alt="DNA"
                             style="height:80px; width:80px; border-radius:50%; object-fit:cover; margin-bottom:15px;">

                        <h5 class="card-title">DNA Sample Management</h5>
                        <p class="card-text">Add, view, edit, and delete DNA samples with ease.</p>
                    </div>
                </div>
            </div>

            <!-- Feature 2 -->
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">

                        <!-- Round Icon -->
                        <img src="images/tax2.png"
                             alt="Taxonomy"
                             style="height:80px; width:80px; border-radius:50%; object-fit:cover; margin-bottom:15px;">

                        <h5 class="card-title">Taxonomy Explorer</h5>
                        <p class="card-text">Browse taxonomy data to understand species relationships.</p>
                    </div>
                </div>
            </div>

            <!-- Feature 3 -->
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">

                        <!-- Round Icon -->
                        <img src="images/user_authen.png"
                             alt="User"
                             style="height:80px; width:80px; border-radius:50%; object-fit:cover; margin-bottom:15px;">

                        <h5 class="card-title">User Authentication</h5>
                        <p class="card-text">Secure login and registration for admins and researchers.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<section class="py-5 bg-white">
    <div class="container text-center">
        <h2 class="mb-4">Platform Statistics</h2>
        <div class="row justify-content-center">

            <!-- Total Users -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm p-4">
                    
                    <!-- Round User Icon -->
                    <img src="images/user.png"
                         alt="Users"
                         style="height:80px; width:80px; border-radius:50%; object-fit:cover; margin-bottom:15px; margin-left:145px;">

                    <h3 class="fw-bold"><?= $user_count ?></h3>
                    <p class="text-muted">Registered Users</p>
                </div>
            </div>

            <!-- Total Species -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm p-4">

                    <!-- Round Species Icon -->
                    <img src="images/species.jpg"
                         alt="Species"
                         style="height:80px; width:80px; border-radius:50%; object-fit:cover; margin-bottom:15px; margin-left:140px">

                    <h3 class="fw-bold"><?= $species_count ?></h3>
                    <p class="text-muted">Species in Database</p>
                </div>
            </div>

        </div>
    </div>
</section>

    <!-- Footer -->
<footer class="bg-success text-white pt-5 pb-3 mt-5">
    <div class="container">
        <div class="row">

            <!-- Logo + About -->
            <div class="col-md-4 mb-4">
                <h5 class="fw-bold">EDNA Biodiversity</h5>
                <p class="small">
                    A platform to explore environmental DNA samples, taxonomy, and biodiversity research tools.
                </p>
            </div>

            <!-- Quick Links -->
            <div class="col-md-2 mb-4">
                <h6 class="fw-bold">Quick Links</h6>
                <ul class="list-unstyled small">
                    <li><a href="index.php" class="text-white text-decoration-none">Home</a></li>
                    <li><a href="upload.php" class="text-white text-decoration-none">Upload</a></li>
                    <li><a href="my_result.php" class="text-white text-decoration-none">My Results</a></li>
                </ul>
            </div>

            <!-- User Links -->
            <div class="col-md-2 mb-4">
                <h6 class="fw-bold">Account</h6>
                <ul class="list-unstyled small">
                    <li><a href="login.php" class="text-white text-decoration-none">Login</a></li>
                    <li><a href="register.php" class="text-white text-decoration-none">Register</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div class="col-md-4 mb-4">
                <h6 class="fw-bold">Contact Us</h6>
                <p class="small">Email: support@edna-biodiversity.com</p>
                <p class="small">Phone: +91-9876543210</p>

                <!-- Social icons -->
                <div class="d-flex gap-3">
                    <a href="#" class="text-white"><i class="bi bi-facebook fs-5"></i></a>
                    <a href="#" class="text-white"><i class="bi bi-twitter fs-5"></i></a>
                    <a href="#" class="text-white"><i class="bi bi-instagram fs-5"></i></a>
                    <a href="#" class="text-white"><i class="bi bi-github fs-5"></i></a>
                </div>
            </div>
        </div>

        <hr class="border-light">

        <div class="text-center small">
            &copy; <?= date('Y') ?> EDNA Biodiversity. All Rights Reserved.
        </div>
    </div>
</footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
