<?php
require 'auth.php';
requireAdminLogin();
require 'dbconnect.php';

$errors = [];
$submittedType = $_POST['search_type'] ?? '';
$submittedTerm = $_POST['search_term'] ?? '';
$searchType = is_string($submittedType) ? trim($submittedType) : '';
$searchTerm = is_string($submittedTerm) ? trim($submittedTerm) : '';
$results = [];
$clubMembers = [];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $errors[] = 'Invalid request.';
} else {
    if (!in_array($searchType, ['participant', 'club'], true)) {
        $errors[] = 'Invalid search type.';
    }

    if ($searchTerm === '') {
        $errors[] = 'Please enter a search term.';
    } elseif (strlen($searchTerm) > 100) {
        $errors[] = 'Search terms must be 100 characters or fewer.';
    }

    if (empty($errors)) {
        try {
            $pdo = getDbConnection();

            if ($searchType === 'participant') {
                $stmt = $pdo->prepare("SELECT p.id, p.firstname, p.surname, p.email, p.power_output, p.distance, c.name AS club_name FROM participant p LEFT JOIN club c ON c.id = p.club_id WHERE p.firstname LIKE :first_term OR p.surname LIKE :surname_term OR CONCAT(p.firstname, ' ', p.surname) LIKE :full_term ORDER BY p.surname, p.firstname");
                $term = '%' . $searchTerm . '%';
                $stmt->execute(['first_term' => $term, 'surname_term' => $term, 'full_term' => $term]);
                $results = $stmt->fetchAll();
            } elseif ($searchType === 'club') {
                $stmt = $pdo->prepare('SELECT c.id, c.name, c.location, COUNT(p.id) AS participant_count, COALESCE(SUM(p.distance), 0) AS total_distance, COALESCE(SUM(p.power_output), 0) AS total_power_output, COALESCE(AVG(p.distance), 0) AS average_distance, COALESCE(AVG(p.power_output), 0) AS average_power_output FROM club c LEFT JOIN participant p ON p.club_id = c.id WHERE c.name LIKE :term GROUP BY c.id, c.name, c.location ORDER BY c.name');
                $stmt->execute(['term' => '%' . $searchTerm . '%']);
                $results = $stmt->fetchAll();

                if (!empty($results)) {
                    $placeholders = [];
                    $parameters = [];
                    foreach ($results as $index => $club) {
                        $placeholder = 'club_id_' . $index;
                        $placeholders[] = ':' . $placeholder;
                        $parameters[$placeholder] = (int)$club['id'];
                    }
                    $membersStmt = $pdo->prepare('SELECT id, firstname, surname, email, power_output, distance, club_id FROM participant WHERE club_id IN (' . implode(', ', $placeholders) . ') ORDER BY club_id, surname, firstname');
                    $membersStmt->execute($parameters);
                    foreach ($membersStmt->fetchAll() as $member) {
                        $clubMembers[(int)$member['club_id']][] = $member;
                    }
                }
            }
        } catch (PDOException $e) {
            $errors[] = 'Search is temporarily unavailable. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Search results</title>
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
                    <a class="nav-link text-light" href="search_form.php">Back to Search</a>
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
                    <p class="text-uppercase text-primary fw-semibold mb-2">Results</p>
                    <h1 class="section-title mb-0"><?php echo $searchType === 'club' ? 'Club search results' : 'Participant search results'; ?></h1>
                </div>
                <a href="search_form.php" class="btn btn-primary">Back to Search</a>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($searchType === 'participant' && empty($errors)): ?>
                <?php if (empty($results)): ?>
                    <div class="alert alert-info" role="status">
                        No current participants found for "<?php echo htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8'); ?>".
                    </div>
                <?php else: ?>
                    <div class="table-responsive card-panel p-0">
                        <table class="table table-striped table-hover mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>First Name</th>
                                    <th>Surname</th>
                                    <th>Email</th>
                                    <th>Power Output</th>
                                    <th>Distance</th>
                                    <th>Club</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results as $participant): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars((string)$participant['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string)$participant['firstname'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string)$participant['surname'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string)$participant['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string)$participant['power_output'], ENT_QUOTES, 'UTF-8'); ?> W</td>
                                        <td><?php echo htmlspecialchars((string)$participant['distance'], ENT_QUOTES, 'UTF-8'); ?> KM</td>
                                        <td><?php echo htmlspecialchars((string)($participant['club_name'] ?? 'Individual'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="edit_participant.php?id=<?php echo (int)$participant['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                <a href="delete.php?id=<?php echo (int)$participant['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this participant? You will be asked to confirm again.');">Delete</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

            <?php endif; ?>

            <?php if ($searchType === 'club' && empty($errors)): ?>
                <?php if (empty($results)): ?>
                    <div class="alert alert-info" role="alert">No clubs found for "<?php echo htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8'); ?>".</div>
                <?php else: ?>
                    <?php foreach ($results as $club): ?>
                        <div class="card-panel mb-4">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                <div>
                                    <h2 class="h4 mb-1"><?php echo htmlspecialchars((string)$club['name'], ENT_QUOTES, 'UTF-8'); ?></h2>
                                    <p class="text-muted mb-0"><?php echo htmlspecialchars((string)$club['location'], ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6 col-xl-3">
                                    <div class="stats-card h-100">
                                        <div class="stat-label">Total Distance</div>
                                        <div class="stat-value"><?php echo number_format((float)$club['total_distance'], 1, '.', ','); ?> KM</div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-3">
                                    <div class="stats-card h-100">
                                        <div class="stat-label">Total Power Output</div>
                                        <div class="stat-value"><?php echo number_format((float)$club['total_power_output'], 1, '.', ','); ?> W</div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-3">
                                    <div class="stats-card h-100">
                                        <div class="stat-label">Average Distance</div>
                                        <div class="stat-value"><?php echo number_format((float)$club['average_distance'], 1, '.', ','); ?> KM</div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-3">
                                    <div class="stats-card h-100">
                                        <div class="stat-label">Average Power Output</div>
                                        <div class="stat-value"><?php echo number_format((float)$club['average_power_output'], 1, '.', ','); ?> W</div>
                                    </div>
                                </div>
                            </div>

                            <?php if (!empty($clubMembers[(int)$club['id']])): ?>
                                <h3 class="h5 mb-3">Club participants</h3>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover mb-0 align-middle">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>First Name</th>
                                                <th>Surname</th>
                                                <th>Email</th>
                                                <th>Distance</th>
                                                <th>Power Output</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($clubMembers[(int)$club['id']] as $member): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars((string)$member['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars((string)$member['firstname'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars((string)$member['surname'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars((string)$member['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars((string)$member['distance'], ENT_QUOTES, 'UTF-8'); ?> KM</td>
                                                    <td><?php echo htmlspecialchars((string)$member['power_output'], ENT_QUOTES, 'UTF-8'); ?> W</td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info mb-0" role="alert">This club currently has no participants.</div>
                            <?php endif; ?>

                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endif; ?>
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