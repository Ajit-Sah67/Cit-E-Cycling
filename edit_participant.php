<?php
require 'auth.php';
requireAdminLogin();
require 'dbconnect.php';

$errors = [];
$success = '';
$participant = null;
$participantChoices = [];
$selectionLoadError = '';
$formValues = [
    'power_output' => '',
    'distance' => '',
];
$idInput = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['id'] ?? null) : ($_GET['id'] ?? null);
$id = is_string($idInput) ? filter_var($idInput, FILTER_VALIDATE_INT) : false;
$powerOutput = is_string($_POST['power_output'] ?? null) ? trim($_POST['power_output']) : '';
$distance = is_string($_POST['distance'] ?? null) ? trim($_POST['distance']) : '';
$formValues['power_output'] = $powerOutput;
$formValues['distance'] = $distance;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($id === false || $id <= 0) {
        $errors[] = 'Invalid participant ID.';
    }

    if ($powerOutput === '') {
        $errors[] = 'Please enter power output.';
    } elseif (!is_numeric($powerOutput) || !is_finite((float)$powerOutput) || (float)$powerOutput < 0 || (float)$powerOutput > 5000) {
        $errors[] = 'Power output must be a finite number from 0 to 5000.';
    }

    if ($distance === '') {
        $errors[] = 'Please enter distance travelled.';
    } elseif (!is_numeric($distance) || !is_finite((float)$distance) || (float)$distance < 0 || (float)$distance > 1000) {
        $errors[] = 'Distance must be a finite number from 0 to 1000.';
    }
} elseif ($idInput !== null && ($id === false || $id <= 0)) {
    $errors[] = 'Invalid participant ID.';
}

try {
    $pdo = getDbConnection();
    if ($idInput === null && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        $choicesStmt = $pdo->prepare('SELECT id, firstname, surname, email, power_output, distance FROM participant ORDER BY surname, firstname');
        $choicesStmt->execute();
        $participantChoices = $choicesStmt->fetchAll();
    } elseif ($id !== false && $id > 0) {
        $loadParticipant = $pdo->prepare('SELECT p.id, p.firstname, p.surname, p.email, c.name AS club_name, p.power_output, p.distance FROM participant p LEFT JOIN club c ON c.id = p.club_id WHERE p.id = :id LIMIT 1');
        $loadParticipant->execute(['id' => (int)$id]);
        $participant = $loadParticipant->fetch();

        if (!$participant) {
            $errors[] = 'Participant not found.';
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)) {
            $update = $pdo->prepare('UPDATE participant SET power_output = :power_output, distance = :distance WHERE id = :id');
            $update->execute([
                'power_output' => (float)$powerOutput,
                'distance' => (float)$distance,
                'id' => (int)$id,
            ]);
            $loadParticipant->execute(['id' => (int)$id]);
            $participant = $loadParticipant->fetch();
            if ($participant) {
                $success = 'Participant scores updated: ' . (string)$participant['power_output'] . ' W and ' . (string)$participant['distance'] . ' KM.';
                $formValues['power_output'] = (string)$participant['power_output'];
                $formValues['distance'] = (string)$participant['distance'];
            } else {
                $errors[] = 'Participant not found.';
            }
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $participant['power_output'] = $powerOutput;
            $participant['distance'] = $distance;
        } else {
            $formValues['power_output'] = (string)$participant['power_output'];
            $formValues['distance'] = (string)$participant['distance'];
        }
    }
} catch (PDOException $e) {
    $selectionLoadError = 'The database is temporarily unavailable. Please try again later.';
    $participant = null;
    $participantChoices = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Update participant</title>
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
                    <h1 class="section-title mb-0"><?php echo $participant === null && $_SERVER['REQUEST_METHOD'] !== 'POST' ? 'Choose a participant to edit' : 'Update participant scores'; ?></h1>
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
            <?php elseif (!empty($success)): ?>
                <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <?php if ($selectionLoadError !== ''): ?>
                <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($selectionLoadError, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <?php if ($participant !== null): ?>
                <?php include 'edit_participant_form.php'; ?>
            <?php elseif (empty($errors) && $_SERVER['REQUEST_METHOD'] !== 'POST' && $selectionLoadError === ''): ?>
                <?php if (empty($participantChoices)): ?>
                    <div class="alert alert-info" role="status">There are no participant records available to edit. Interest registrations are stored separately; load participant records into the participant table to continue.</div>
                <?php else: ?>
                    <div class="table-responsive card-panel p-0">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">Name</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Power Output</th>
                                    <th scope="col">Distance</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($participantChoices as $choice): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars((string)$choice['firstname'] . ' ' . (string)$choice['surname'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string)$choice['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string)$choice['power_output'], ENT_QUOTES, 'UTF-8'); ?> W</td>
                                        <td><?php echo htmlspecialchars((string)$choice['distance'], ENT_QUOTES, 'UTF-8'); ?> KM</td>
                                        <td><a href="edit_participant.php?id=<?php echo (int)$choice['id']; ?>" class="btn btn-sm btn-primary">Edit Scores</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="card-panel">
                    <p class="mb-0">Review the message above, then return to the participant list to continue.</p>
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