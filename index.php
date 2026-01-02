<?php

declare(strict_types=1);
require __DIR__ . '/includes/util.php';
$data = load_data();
$hospitals = hospitals_options($data);
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'login_patient') {
        $email = isset($_POST['email']) ? trim((string)$_POST['email']) : '';
        $password = isset($_POST['password']) ? (string)$_POST['password'] : '';
        if ($email !== '' && $password !== '') {
            $patient = find_patient_by_email_password($data, $email, $password);
            if ($patient) {
                header('Location: patients/dashboard.php?view=appointments&patient_id=' . urlencode($patient['id']));
                exit;
            } else {
                $message = 'Invalid email or password.';
            }
        }
    } elseif ($_POST['action'] === 'signup_patient') {
        $name = isset($_POST['name']) ? trim((string)$_POST['name']) : '';
        $email = isset($_POST['email']) ? trim((string)$_POST['email']) : '';
        $password = isset($_POST['password']) ? (string)$_POST['password'] : '';
        $hospitalId = isset($_POST['hospital_id']) ? trim((string)$_POST['hospital_id']) : '';
        if ($name !== '' && $email !== '' && $password !== '' && $hospitalId !== '') {
            $patient = add_patient($data, $name, $hospitalId, $email, $password);
            save_data($data);
            header('Location: patients/dashboard.php?view=request&patient_id=' . urlencode($patient['id']));
            exit;
        }
    }
}
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hospital Management System</title>
    <link rel="stylesheet" href="assets/styles.css">
    <style>
    .grid-2 {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    @media (max-width: 760px) {
        .grid-2 {
            grid-template-columns: 1fr;
        }
    }
    </style>
</head>

<body>
    <header class="header">
        <h1>Hospital Management System</h1>
    </header>
    <main class="container">
        <section class="panel">
            <h2>Patient</h2>
            <div class="form" style="display:flex;justify-content:flex-start;">
                <div class="pill-tabs">
                    <a href="#" class="pill active" id="btnPatientLogin">Login</a>
                    <a href="#" class="pill" id="btnPatientSignup">Sign Up</a>
                </div>
            </div>
            <?php if ($message): ?><div class="form">
                <div class="muted"><?php echo htmlspecialchars($message); ?></div>
            </div><?php endif; ?>
            <div class="form grid-2">
                <form id="patientLoginForm" method="post" class="form"
                    style="background:#0b1021;border:1px solid #1f2937;border-radius:8px;">
                    <input type="hidden" name="action" value="login_patient">
                    <div class="form-row">
                        <label>Email</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="form-row">
                        <label>Password</label>
                        <input type="password" name="password" required>
                    </div>
                    <div class="form-row">
                        <button type="submit" class="btn">Login</button>
                    </div>
                </form>
                <form id="patientSignupForm" method="post" class="form hidden"
                    style="background:#0b1021;border:1px solid #1f2937;border-radius:8px;">
                    <input type="hidden" name="action" value="signup_patient">
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
                        <label>Hospital</label>
                        <select name="hospital_id" required>
                            <?php foreach ($hospitals as $hid => $hname): ?>
                            <option value="<?php echo htmlspecialchars($hid); ?>">
                                <?php echo htmlspecialchars($hname); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-row">
                        <button type="submit" class="btn">Sign Up</button>
                    </div>
                </form>
            </div>
        </section>
        <section class="panel">
            <h2>Staff</h2>
            <div class="form">
                <p class="muted">Are you staff? <a href="staff/login.php">Login here</a>. For admins, use <a
                        href="admin/login.php">Admin login</a>.</p>
            </div>
        </section>
    </main>
    <script>
    const btnPatientLogin = document.getElementById('btnPatientLogin');
    const btnPatientSignup = document.getElementById('btnPatientSignup');
    const patientLoginForm = document.getElementById('patientLoginForm');
    const patientSignupForm = document.getElementById('patientSignupForm');
    btnPatientLogin.addEventListener('click', function(e) {
        e.preventDefault();
        btnPatientLogin.classList.add('active');
        btnPatientSignup.classList.remove('active');
        patientLoginForm.classList.remove('hidden');
        patientSignupForm.classList.add('hidden');
    });
    btnPatientSignup.addEventListener('click', function(e) {
        e.preventDefault();
        btnPatientSignup.classList.add('active');
        btnPatientLogin.classList.remove('active');
        patientSignupForm.classList.remove('hidden');
        patientLoginForm.classList.add('hidden');
    });
    </script>
</body>

</html>