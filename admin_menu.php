<?php
require 'auth.php';
requireAdminLogin();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="admin-page">
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="admin_menu.php">
                <span class="brand-mark">C</span>
                <span>CIT-E CYCLING</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="adminNavbar">
                <div class="navbar-nav ms-auto align-items-lg-center gap-lg-3">
                    <a class="nav-link text-light" href="search_form.php">Search</a>
                    <a class="nav-link text-light" href="view_participants_edit_delete.php">Participants</a>
                    <a class="nav-link text-light" href="view_interest.php">Interest registrations</a>
                    <form action="logout.php" method="post">
                        <button type="submit" class="btn btn-outline-light">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <main class="py-5">
        <div class="container">
            <div class="dashboard-intro d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div>
                    <p class="text-uppercase text-primary fw-semibold mb-2">Operations panel</p>
                    <h1 class="section-title mb-1">CIT-E CYCLING</h1>
                    <h2 class="h4 text-white mb-0">ADMIN DASHBOARD</h2>
                    <p class="text-white-50 mt-2 mb-0">Welcome, <?php echo htmlspecialchars((string)($_SESSION['admin_username'] ?? 'admin'), ENT_QUOTES, 'UTF-8'); ?>.</p>
                </div>
                <form action="logout.php" method="post">
                    <button type="submit" class="btn btn-danger">Logout</button>
                </form>
            </div>

            <p class="text-muted mb-4">Manage participant data, perform searches, and handle club insights from a single secure dashboard.</p>

            <div class="row g-4">
                <div class="col-md-6 col-xl-4">
                    <a href="search_form.php" class="text-decoration-none">
                        <div class="dashboard-card h-100">
                            <div class="feature-icon bg-light text-primary mb-3"><i class="bi bi-search"></i></div>
                            <h4>Search Participants / Clubs</h4>
                            <p class="mb-0 text-light-emphasis">Find riders and team summaries using partial or full name searches.</p>
                        </div>
                    </a>
                </div>
                <div class="col-md-6 col-xl-4">
                    <a href="view_participants_edit_delete.php" class="text-decoration-none">
                        <div class="dashboard-card h-100">
                            <div class="feature-icon bg-light text-primary mb-3"><i class="bi bi-people"></i></div>
                            <h4>View Participants</h4>
                            <p class="mb-0 text-light-emphasis">Review all rider records and identify data that needs attention.</p>
                        </div>
                    </a>
                </div>
                <div class="col-md-6 col-xl-4">
                    <a href="view_interest.php" class="text-decoration-none">
                        <div class="dashboard-card h-100">
                            <div class="feature-icon bg-light text-primary mb-3"><i class="bi bi-envelope-paper"></i></div>
                            <h4>View Interest Registrations</h4>
                            <p class="mb-0 text-light-emphasis">Review people who have registered interest in future cycling events.</p>
                        </div>
                    </a>
                </div>
                <div class="col-md-6 col-xl-4">
                    <a href="edit_participant.php" class="text-decoration-none">
                        <div class="dashboard-card h-100">
                            <div class="feature-icon bg-light text-primary mb-3"><i class="bi bi-pencil-square"></i></div>
                            <h4>Edit Participants</h4>
                            <p class="mb-0 text-light-emphasis">Choose a participant and update their power output and travelled distance.</p>
                        </div>
                    </a>
                </div>
                <div class="col-md-6 col-xl-4">
                    <a href="view_participants_edit_delete.php" class="text-decoration-none">
                        <div class="dashboard-card h-100">
                            <div class="feature-icon bg-light text-primary mb-3"><i class="bi bi-trash3"></i></div>
                            <h4>Delete Participants</h4>
                            <p class="mb-0 text-light-emphasis">Remove users only after verification to prevent accidental deletions.</p>
                        </div>
                    </a>
                </div>
                <div class="col-md-6 col-xl-4">
                    <form action="logout.php" method="post" class="h-100">
                        <button type="submit" class="dashboard-card w-100 text-start border-0 h-100">
                            <div class="feature-icon bg-light text-primary mb-3"><i class="bi bi-box-arrow-right"></i></div>
                            <h4>Logout</h4>
                            <p class="mb-0 text-light-emphasis">Close the current session and return to the secure login page.</p>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="row align-items-center g-3">
                <div class="col-md-6">
                    <h5 class="mb-1">Cit-E Cycling</h5>
                    <p class="mb-0 text-light-emphasis">Power. Distance. Performance.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="text-light-emphasis">
                    </span>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>