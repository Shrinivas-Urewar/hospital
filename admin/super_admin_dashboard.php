<?php
declare(strict_types=1);
require __DIR__ . '/../includes/util.php';
$data = load_data();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_admin') {
    $name = str('name');
    $email = str('email');
    $password = str('password');
    if ($name !== '' && $email !== '' && $password !== '') {
        add_admin($data, $name, $email, $password);
        save_data($data);
    }
    redirect('super_admin_dashboard.php');
}
$stats = global_stats($data);
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Super Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
    <header class="header">
        <h1>Super Admin Dashboard</h1>
        <nav class="nav">
            <a href="super_admin_dashboard.php">Global</a>
            <a href="admin_dashboard.php">Admin</a>
        </nav>
    </header>
    <main class="container">
        <section class="cards">
            <div class="card"><div class="card-title">Hospitals</div><div class="card-value"><?php echo $stats['hospitals']; ?></div></div>
            <div class="card"><div class="card-title">Admins</div><div class="card-value"><?php echo $stats['admins']; ?></div></div>
            <div class="card"><div class="card-title">Doctors</div><div class="card-value"><?php echo $stats['doctors']; ?></div></div>
            <div class="card"><div class="card-title">Attendants</div><div class="card-value"><?php echo $stats['attendants']; ?></div></div>
            <div class="card"><div class="card-title">Patients</div><div class="card-value"><?php echo $stats['patients']; ?></div></div>
            <div class="card"><div class="card-title">Appointments</div><div class="card-value"><?php echo $stats['appointments']; ?></div></div>
        </section>
        <section class="panel">
            <h2>Admins</h2>
            <table class="table">
                <thead>
                    <tr><th>Name</th><th>Email</th><th>ID</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($data['admins'] as $a): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($a['name']); ?></td>
                            <td><?php echo htmlspecialchars($a['email']); ?></td>
                            <td class="muted"><?php echo htmlspecialchars($a['id']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (count($data['admins']) === 0): ?>
                        <tr><td colspan="3" class="muted center">No admins yet</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
        <section class="panel">
            <h2>Add New Admin</h2>
            <form method="post" class="form">
                <input type="hidden" name="action" value="add_admin">
                <div class="form-row">
                    <label>Name</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-row">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-row">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-row">
                    <button type="submit" class="btn">Add Admin</button>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
