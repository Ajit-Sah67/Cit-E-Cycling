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
    <title>Search</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
                    <a class="nav-link text-light" href="admin_menu.php">Dashboard</a>
                    <form action="logout.php" method="post">
                        <button type="submit" class="btn btn-outline-light">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <main class="py-5">
        <div class="container">
            <div class="mb-4">
                <p class="text-uppercase text-primary fw-semibold mb-2">Search tools</p>
                <h1 class="section-title mb-0">Participant and club search</h1>
            </div>

            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card-panel h-100">
                        <h2 class="h4 mb-3">Search for an individual participant</h2>
                        <form action="search_result.php" method="POST">
                            <input type="hidden" name="search_type" value="participant">
                            <p class="text-muted">Searches current participant first names and surnames, including partial matches.</p>
                            <div class="mb-3">
                                <label for="participant-search" class="form-label">Participant first name or surname</label>
                                <input type="text" class="form-control" id="participant-search" name="search_term" maxlength="100" placeholder="e.g. Lorette or Lamacraft" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Search Participants</button>
                        </form>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card-panel h-100">
                        <h2 class="h4 mb-3">Search for a club / team</h2>
                        <form action="search_result.php" method="POST">
                            <input type="hidden" name="search_type" value="club">
                            <p class="text-muted">Searches club names and returns club members with performance totals and averages.</p>
                            <div class="mb-3">
                                <label for="club-search" class="form-label">Club name</label>
                                <input type="text" class="form-control" id="club-search" name="search_term" maxlength="100" placeholder="e.g. Roker Rollers" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Search Clubs</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container text-center text-md-start">
            <p class="mb-0">Cit-E Cycling · Power. Distance. Performance.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>