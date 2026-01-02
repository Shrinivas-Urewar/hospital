<?php

declare(strict_types=1);
require __DIR__ . '/../includes/util.php';
$data = load_data();
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'admin_login') {
    $id = isset($_POST['id']) ? trim((string)$_POST['id']) : '';
    $password = isset($_POST['password']) ? (string)$_POST['password'] : '';
    if ($id !== '' && $password !== '') {
        $admin = find_admin_by_id_password($data, $id, $password);
        if ($admin) {
            header('Location: admin_dashboard.php');
            exit;
        }
        $message = 'Invalid ID or password';
    }
}
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>

<body>
    <header class="header">
        <h1>Admin Login</h1>
    </header>
    <main class="container">
        <?php if ($message): ?><section class="panel">
                <div class="form">
                    <div class="muted"><?php echo htmlspecialchars($message); ?></div>
                </div>
            </section><?php endif; ?>
        <section class="panel">
            <h2>Enter Details</h2>
            <form method="post" class="form" style="max-width:560px;">
                <input type="hidden" name="action" value="admin_login">

                <div class="form-row">
                    <label>Admin ID</label>
                    <input type="text" name="id" required>
                </div>
                <div class="form-row">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-row">
                    <button type="submit" class="btn">Continue</button>
                </div>
            </form>
        </section>
    </main>
</body>

</html>