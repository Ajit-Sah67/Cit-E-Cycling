<?php
require 'dbconnect.php';

$errors = [];
$values = [
    'firstname' => '',
    'surname' => '',
    'email' => '',
    'club_id' => '',
    'terms' => ''
];
$success = '';
$clubs = [];
$pdo = null;

try {
    $pdo = getDbConnection();
    $clubsStmt = $pdo->prepare('SELECT id, name FROM club ORDER BY name');
    $clubsStmt->execute();
    $clubs = $clubsStmt->fetchAll();
} catch (PDOException $e) {
    $errors[] = 'Registration is temporarily unavailable. Please try again later.';
    $pdo = null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['firstname'] = is_string($_POST['firstname'] ?? null) ? trim($_POST['firstname']) : '';
    $values['surname'] = is_string($_POST['surname'] ?? null) ? trim($_POST['surname']) : '';
    $values['email'] = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
    $rawClubId = $_POST['club_id'] ?? '';
    $values['club_id'] = is_string($rawClubId) ? trim($rawClubId) : '';
    $values['terms'] = (($_POST['terms'] ?? null) === 'yes') ? 'yes' : '';

    if (!is_string($rawClubId)) {
        $errors[] = 'Please select an available cycling club or choose Individual.';
    }

    if ($values['firstname'] === '') {
        $errors[] = 'Please enter your first name.';
    } elseif (!preg_match("/^[A-Za-z][A-Za-z\\s'\\-]{1,49}$/", $values['firstname'])) {
        $errors[] = 'First name must be 2-50 characters and contain only letters, spaces, hyphens, or apostrophes.';
    }

    if ($values['surname'] === '') {
        $errors[] = 'Please enter your surname.';
    } elseif (!preg_match("/^[A-Za-z][A-Za-z\\s'\\-]{1,49}$/", $values['surname'])) {
        $errors[] = 'Surname must be 2-50 characters and contain only letters, spaces, hyphens, or apostrophes.';
    }

    if ($values['email'] === '') {
        $errors[] = 'Please enter your email address.';
    } elseif (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } elseif (strlen($values['email']) > 100) {
        $errors[] = 'Email address must be 100 characters or fewer.';
    }

    if ($values['terms'] !== 'yes') {
        $errors[] = 'You must accept the terms and conditions.';
    }

    $clubId = null;
    if ($values['club_id'] !== '' && $pdo instanceof PDO) {
        $validatedClubId = filter_var($values['club_id'], FILTER_VALIDATE_INT);
        $clubIds = array_map(static function (array $club): int {
            return (int)$club['id'];
        }, $clubs);

        if ($validatedClubId === false || !in_array($validatedClubId, $clubIds, true)) {
            $errors[] = 'Please select an available cycling club or choose Individual.';
        } else {
            $clubId = $validatedClubId;
        }
    }

    if (empty($errors) && $pdo instanceof PDO) {
        try {
            $existingInterest = $pdo->prepare('SELECT id FROM interest WHERE email = :email LIMIT 1');
            $existingInterest->execute(['email' => $values['email']]);

            if ($existingInterest->fetch()) {
                $errors[] = 'This email address has already registered interest.';
            } else {
                $pdo->beginTransaction();

                $interestStmt = $pdo->prepare('INSERT INTO interest (firstname, surname, email, terms, club_id) VALUES (:firstname, :surname, :email, :terms, :club_id)');
                $interestStmt->execute([
                    'firstname' => $values['firstname'],
                    'surname' => $values['surname'],
                    'email' => $values['email'],
                    'terms' => 1,
                    'club_id' => $clubId,
                ]);

                $participantStmt = $pdo->prepare('INSERT INTO participant (firstname, surname, email, power_output, distance, club_id) VALUES (:firstname, :surname, :email, 0, 0, :club_id)');
                $participantStmt->execute([
                    'firstname' => $values['firstname'],
                    'surname' => $values['surname'],
                    'email' => $values['email'],
                    'club_id' => $clubId,
                ]);

                $pdo->commit();
                $success = 'Thank you, your interest has been registered.';
                $values = ['firstname' => '', 'surname' => '', 'email' => '', 'club_id' => '', 'terms' => ''];
            }
        } catch (PDOException $e) {
            if ($pdo instanceof PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors[] = 'Unable to register your interest right now. Please try again later.';
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
    <title>Register your interest</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="index.html">
                <span class="brand-mark">C</span>
                <span>Cit-E Cycling</span>
            </a>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-light" href="index.html">Back to Home</a>
            </div>
        </div>
    </nav>

    <main class="auth-shell">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="auth-card p-4 p-md-5">
                        <div class="mb-4">
                            <p class="text-uppercase text-info fw-semibold mb-2">Future events</p>
                            <h1 class="h2 mb-1">Register interest</h1>
                            <p class="text-light-emphasis mb-0">Register your interest to receive updates about future Cit-E Cycling events.</p>
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

                        <form action="register.php" method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="firstname" class="form-label">First name</label>
                                <input type="text" class="form-control" id="firstname" name="firstname" minlength="2" maxlength="50" pattern="[A-Za-z][A-Za-z\s'\-]{1,49}" value="<?php echo htmlspecialchars($values['firstname'], ENT_QUOTES, 'UTF-8'); ?>" required>
                                <div class="invalid-feedback">Enter 2-50 characters using letters, spaces, hyphens, or apostrophes.</div>
                            </div>

                            <div class="mb-3">
                                <label for="surname" class="form-label">Surname</label>
                                <input type="text" class="form-control" id="surname" name="surname" minlength="2" maxlength="50" pattern="[A-Za-z][A-Za-z\s'\-]{1,49}" value="<?php echo htmlspecialchars($values['surname'], ENT_QUOTES, 'UTF-8'); ?>" required>
                                <div class="invalid-feedback">Enter 2-50 characters using letters, spaces, hyphens, or apostrophes.</div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" maxlength="100" placeholder="you@example.com" value="<?php echo htmlspecialchars($values['email'], ENT_QUOTES, 'UTF-8'); ?>" required>
                                <div class="invalid-feedback">Enter a valid email address of 100 characters or fewer.</div>
                            </div>

                            <div class="mb-3">
                                <label for="club_id" class="form-label">Competition entry</label>
                                <select class="form-select" id="club_id" name="club_id">
                                    <option value="" <?php echo $values['club_id'] === '' ? 'selected' : ''; ?>>Individual (no club)</option>
                                    <?php foreach ($clubs as $club): ?>
                                        <option value="<?php echo (int)$club['id']; ?>" <?php echo $values['club_id'] === (string)$club['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars((string)$club['name'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text text-light-emphasis">Choose a club to register as part of that group, or leave Individual selected.</div>
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" value="yes" id="terms" name="terms" <?php echo ($values['terms'] === 'yes') ? 'checked' : ''; ?> required>
                                <label class="form-check-label" for="terms">
                                    I accept the terms and conditions.
                                </label>
                                <div class="invalid-feedback">You must accept the terms and conditions.</div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Submit Registration</button>
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
    <script>
        (function () {
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }());
    </script>
</body>
</html>