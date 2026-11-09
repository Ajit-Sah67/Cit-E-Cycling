<?php
require 'auth.php';
requireAdminLogin();
require 'dbconnect.php';

$participants = [];
$databaseError = '';

try {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare('SELECT p.id, p.firstname, p.surname, p.email, p.power_output, p.distance, c.name AS club_name FROM participant p LEFT JOIN club c ON c.id = p.club_id ORDER BY p.surname, p.firstname');
    $stmt->execute();
    $participants = $stmt->fetchAll();
} catch (PDOException $e) {
    $databaseError = 'Something went wrong while loading participants.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Participant list</title>
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
                    <p class="text-uppercase text-primary fw-semibold mb-2">Rider data</p>
                    <h1 class="section-title mb-0">Participant list</h1>
                    <p class="text-muted mt-2 mb-0"><?php echo count($participants); ?> participant(s)</p>
                </div>
                <a href="admin_menu.php" class="btn btn-primary">Back to Dashboard</a>
            </div>

            <?php if ($databaseError !== ''): ?>
                <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($databaseError, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php elseif (empty($participants)): ?>
                <div class="alert alert-info" role="status">No participant records were found. Interest registrations are stored separately; load participant records into the participant table to use search, edit, and delete.</div>
            <?php else: ?>
                <form id="participant-selection-form" action="delete.php" method="POST">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" id="select-all-participants" class="btn btn-outline-primary">Select All</button>
                            <button type="button" id="clear-participant-selection" class="btn btn-outline-secondary">Clear Selection</button>
                            <span id="selected-participant-count" class="text-muted" aria-live="polite">0 selected</span>
                        </div>
                        <button type="submit" class="btn btn-danger">Delete Selected</button>
                    </div>

                    <div class="table-responsive card-panel p-0 d-none d-xl-block">
                        <table class="table table-striped table-hover mb-0 align-middle participant-table">
                            <thead>
                                <tr>
                                    <th scope="col"><span class="visually-hidden">Select</span></th>
                                    <th scope="col">ID</th>
                                    <th scope="col">First Name</th>
                                    <th scope="col">Surname</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Power Output</th>
                                    <th scope="col">Distance</th>
                                    <th scope="col">Club</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($participants as $participant): ?>
                                    <tr>
                                        <td>
                                            <input class="form-check-input participant-checkbox" type="checkbox" name="participant_ids[]" value="<?php echo (int)$participant['id']; ?>" aria-label="Select participant <?php echo htmlspecialchars((string)$participant['firstname'] . ' ' . (string)$participant['surname'], ENT_QUOTES, 'UTF-8'); ?>">
                                        </td>
                                        <td><?php echo (int)$participant['id']; ?></td>
                                        <td><?php echo htmlspecialchars((string)$participant['firstname'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string)$participant['surname'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string)$participant['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string)$participant['power_output'], ENT_QUOTES, 'UTF-8'); ?> W</td>
                                        <td><?php echo htmlspecialchars((string)$participant['distance'], ENT_QUOTES, 'UTF-8'); ?> KM</td>
                                        <td><?php echo htmlspecialchars((string)($participant['club_name'] ?? 'Individual'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-2">
                                                <a href="edit_participant.php?id=<?php echo (int)$participant['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                <a href="delete.php?id=<?php echo (int)$participant['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this participant? You will be asked to confirm again.');">Delete</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-xl-none">
                        <div class="row g-3">
                            <?php foreach ($participants as $participant): ?>
                                <div class="col-12 col-md-6">
                                    <article class="participant-card p-3 h-100">
                                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                            <div>
                                                <div class="participant-name"><?php echo htmlspecialchars((string)$participant['firstname'] . ' ' . (string)$participant['surname'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                <div class="text-muted small">Participant #<?php echo (int)$participant['id']; ?></div>
                                            </div>
                                            <input class="form-check-input participant-checkbox mt-1" type="checkbox" name="participant_ids[]" value="<?php echo (int)$participant['id']; ?>" aria-label="Select participant <?php echo htmlspecialchars((string)$participant['firstname'] . ' ' . (string)$participant['surname'], ENT_QUOTES, 'UTF-8'); ?>">
                                        </div>
                                        <dl class="row small mb-3 participant-data">
                                            <dt class="col-5">Email</dt>
                                            <dd class="col-7"><?php echo htmlspecialchars((string)$participant['email'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                            <dt class="col-5">Power</dt>
                                            <dd class="col-7"><?php echo htmlspecialchars((string)$participant['power_output'], ENT_QUOTES, 'UTF-8'); ?> W</dd>
                                            <dt class="col-5">Distance</dt>
                                            <dd class="col-7"><?php echo htmlspecialchars((string)$participant['distance'], ENT_QUOTES, 'UTF-8'); ?> KM</dd>
                                            <dt class="col-5">Club</dt>
                                            <dd class="col-7"><?php echo htmlspecialchars((string)($participant['club_name'] ?? 'Individual'), ENT_QUOTES, 'UTF-8'); ?></dd>
                                        </dl>
                                        <div class="participant-actions d-flex flex-wrap gap-2">
                                            <a href="edit_participant.php?id=<?php echo (int)$participant['id']; ?>" class="btn btn-sm btn-outline-primary">Edit Scores</a>
                                            <a href="delete.php?id=<?php echo (int)$participant['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this participant? You will be asked to confirm again.');">Delete</a>
                                        </div>
                                    </article>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </main>

    <footer class="footer">
        <div class="container text-center text-md-start">
            <p class="mb-0">Cit-E Cycling · Power. Distance. Performance.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            const checkboxes = Array.from(document.querySelectorAll('.participant-checkbox'));
            const count = document.getElementById('selected-participant-count');
            const form = document.getElementById('participant-selection-form');
            const selectAll = document.getElementById('select-all-participants');
            const clearSelection = document.getElementById('clear-participant-selection');
            if (!form || !selectAll || !clearSelection || !count) {
                return;
            }

            const updateCount = function () {
                const selectedIds = new Set(checkboxes.filter(function (checkbox) {
                    return checkbox.checked;
                }).map(function (checkbox) {
                    return checkbox.value;
                }));
                count.textContent = selectedIds.size + ' selected';
            };

            selectAll.addEventListener('click', function () {
                checkboxes.forEach(function (checkbox) {
                    checkbox.checked = true;
                });
                updateCount();
            });
            clearSelection.addEventListener('click', function () {
                checkboxes.forEach(function (checkbox) {
                    checkbox.checked = false;
                });
                updateCount();
            });
            checkboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', updateCount);
            });
            form.addEventListener('submit', function (event) {
                if (!checkboxes.some(function (checkbox) {
                    return checkbox.checked;
                })) {
                    event.preventDefault();
                    window.alert('Select at least one participant before choosing Delete Selected.');
                } else if (!window.confirm('Delete the selected participants? This action cannot be undone.')) {
                    event.preventDefault();
                }
            });
        }());
    </script>
</body>
</html>
