<?php
require 'auth.php';
requireAdminLogin();
require 'dbconnect.php';

$errors = [];
$success = '';
$participants = [];
$participantIds = [];
$confirmed = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawIds = $_POST['participant_ids'] ?? [];
    if (!is_array($rawIds) && isset($_POST['id'])) {
        $rawIds = [$_POST['id']];
    }

    if (!is_array($rawIds) || count($rawIds) === 0) {
        $errors[] = 'Select at least one participant to delete.';
    } elseif (count($rawIds) > 1000) {
        $errors[] = 'Too many participants were selected. Please select fewer records.';
    } else {
        foreach ($rawIds as $rawId) {
            if (!is_string($rawId) && !is_int($rawId)) {
                $errors[] = 'One or more participant IDs are invalid.';
                break;
            }

            $validatedId = filter_var((string)$rawId, FILTER_VALIDATE_INT);
            if ($validatedId === false || $validatedId <= 0) {
                $errors[] = 'One or more participant IDs are invalid.';
                break;
            }

            $participantIds[] = (int)$validatedId;
        }
        $participantIds = array_values(array_unique($participantIds));
    }

    $confirmed = (($_POST['confirm'] ?? '') === 'yes');

    if (empty($errors)) {
        try {
            $pdo = getDbConnection();
            $placeholders = implode(',', array_fill(0, count($participantIds), '?'));
            $select = $pdo->prepare('SELECT id, firstname, surname, email, power_output, distance FROM participant WHERE id IN (' . $placeholders . ') ORDER BY surname, firstname');
            $select->execute($participantIds);
            $participants = $select->fetchAll();

            if (count($participants) !== count($participantIds)) {
                $errors[] = 'One or more selected participants no longer exist. Reload the participant list and try again.';
            } elseif ($confirmed) {
                $pdo->beginTransaction();
                $delete = $pdo->prepare('DELETE FROM participant WHERE id IN (' . $placeholders . ')');
                $delete->execute($participantIds);

                if ($delete->rowCount() !== count($participantIds)) {
                    throw new RuntimeException('The number of deleted participant records did not match the selection.');
                }

                $pdo->commit();
                $success = count($participantIds) === 1
                    ? 'Participant deleted successfully.'
                    : count($participantIds) . ' participants deleted successfully.';
                $participants = [];
            }
        } catch (Throwable $e) {
            if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors[] = 'Something went wrong while deleting the selected participants. Please try again.';
        }
    }
} else {
    $rawId = $_GET['id'] ?? null;
    $validatedId = is_string($rawId) ? filter_var($rawId, FILTER_VALIDATE_INT) : false;

    if ($validatedId === false || $validatedId === null || $validatedId <= 0) {
        $errors[] = 'Please select a valid participant to delete.';
    } else {
        $participantIds = [(int)$validatedId];
        try {
            $pdo = getDbConnection();
            $stmt = $pdo->prepare('SELECT id, firstname, surname, email, power_output, distance FROM participant WHERE id = :id LIMIT 1');
            $stmt->execute(['id' => (int)$validatedId]);
            $participant = $stmt->fetch();

            if (!$participant) {
                $errors[] = 'Participant not found.';
            } else {
                $participants = [$participant];
            }
        } catch (PDOException $e) {
            $errors[] = 'Something went wrong while loading the participant.';
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
    <title>Delete participants</title>
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
                    <a class="nav-link text-light" href="view_participants_edit_delete.php">Participants</a>
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
                    <p class="text-uppercase text-primary fw-semibold mb-2">Participant management</p>
                    <h1 class="section-title mb-0"><?php echo count($participantIds) > 1 ? 'Delete selected participants' : 'Delete participant'; ?></h1>
                </div>
                <a href="view_participants_edit_delete.php" class="btn btn-primary">Back to Participant List</a>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php elseif ($success !== ''): ?>
                <div class="alert alert-success" role="status"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
                <a href="view_participants_edit_delete.php" class="btn btn-primary">Return to Participants</a>
            <?php elseif (!empty($participants)): ?>
                <div class="card-panel border-danger-subtle">
                    <h2 class="h4 text-danger mb-3">Confirm deletion</h2>
                    <p class="mb-3">The following <?php echo count($participants) === 1 ? 'participant will' : 'participants will'; ?> be permanently removed. Verify the selection before continuing.</p>

                    <div class="table-responsive mb-4">
                        <table class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Power</th>
                                    <th scope="col">Distance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($participants as $participant): ?>
                                    <tr>
                                        <td><?php echo (int)$participant['id']; ?></td>
                                        <td><?php echo htmlspecialchars((string)$participant['firstname'] . ' ' . (string)$participant['surname'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string)$participant['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string)$participant['power_output'], ENT_QUOTES, 'UTF-8'); ?> W</td>
                                        <td><?php echo htmlspecialchars((string)$participant['distance'], ENT_QUOTES, 'UTF-8'); ?> KM</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <form action="delete.php" method="POST">
                        <?php foreach ($participantIds as $participantId): ?>
                            <input type="hidden" name="participant_ids[]" value="<?php echo (int)$participantId; ?>">
                        <?php endforeach; ?>
                        <input type="hidden" name="confirm" value="yes">
                        <div class="d-flex flex-wrap gap-3">
                            <button type="submit" class="btn btn-danger">Confirm Delete</button>
                            <a href="view_participants_edit_delete.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
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
