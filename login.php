<?php
require 'auth.php';
require 'dbconnect.php';

$errors = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedUsername = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $username = is_string($submittedUsername) ? trim($submittedUsername) : '';
    $password = is_string($password) ? $password : '';

    if ($username === '') {
        $errors[] = 'Please enter your username.';
    } elseif (strlen($username) > 10) {
        $errors[] = 'Username must be 10 characters or fewer.';
    }

    if ($password === '') {
        $errors[] = 'Please enter your password.';
    }

    if (empty($errors)) {
        try {
            $pdo = getDbConnection();
            $stmt = $pdo->prepare('SELECT id, username, password FROM user WHERE username = :username LIMIT 1');
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch();

            if ($user && $user['password'] === $password) {
                session_regenerate_id(true);
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $user['username'];
                header('Location: admin_menu.php');
                exit;
            }

            $errors[] = 'Invalid login credentials.';
        } catch (PDOException $e) {
            $errors[] = 'Login is temporarily unavailable. Please try again.';
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
    <title>Admin login</title>
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
                <div class="col-md-6 col-xl-5">
                    <div class="auth-card p-4 p-md-5">
                        <div class="text-center mb-4">
                            <p class="text-uppercase text-info fw-semibold mb-2">Restricted access</p>
                            <h1 class="h2 mb-1">Admin login</h1>
                            <p class="text-light-emphasis mb-0">Enter your secure credentials to continue.</p>
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

                        <form action="login.php" method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" maxlength="10" value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>" required>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="showPassword">
                                    <label class="form-check-label" for="showPassword">Show password</label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Login</button>
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
        const showPassword = document.getElementById('showPassword');
        const password = document.getElementById('password');

        showPassword.addEventListener('change', () => {
            password.type = showPassword.checked ? 'text' : 'password';
        });
    </script>
</body>
</html>