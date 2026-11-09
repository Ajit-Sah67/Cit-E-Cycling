<?php
require 'auth.php';
requireAdminLogin();
require 'dbconnect.php';

$interests = [];
$databaseError = '';

try {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare('SELECT i.id, i.firstname, i.surname, i.email, i.terms, c.name AS club_name FROM interest i LEFT JOIN club c ON c.id = i.club_id ORDER BY i.id DESC');
    $stmt->execute();
    $interests = $stmt->fetchAll();
} catch (PDOException $e) {
    $databaseError = 'Unable to load interest registrations right now.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interest registrations | Cit-E Cycling</title>
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
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div>
                    <p class="text-uppercase text-primary fw-semibold mb-2">Public sign-ups</p>
                    <h1 class="section-title mb-0">Interest registrations</h1>
                    <p class="text-muted mt-2 mb-0"><?php echo count($interests); ?> registration(s)</p>
                </div>
                <a href="admin_menu.php" class="btn btn-primary">Back to Dashboard</a>
            </div>

            <?php if ($databaseError !== ''): ?>
                <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($databaseError, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php elseif (empty($interests)): ?>
                <div class="alert alert-info" role="status">No interest registrations yet.</div>
            <?php else: ?>
                <div class="table-responsive card-panel p-0">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">First Name</th>
                                <th scope="col">Surname</th>
                                <th scope="col">Email</th>
                                <th scope="col">Entry Type</th>
                                <th scope="col">Terms Accepted</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($interests as $interest): ?>
                                <tr>
                                    <td><?php echo (int)$interest['id']; ?></td>
                                    <td><?php echo htmlspecialchars((string)$interest['firstname'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars((string)$interest['surname'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars((string)$interest['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars((string)($interest['club_name'] ?? 'Individual'), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo ((int)$interest['terms'] === 1) ? 'Yes' : 'No'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p class="mb-0">Cit-E Cycling · Power. Distance. Performance.</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
