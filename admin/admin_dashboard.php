<?php

declare(strict_types=1);
require __DIR__ . '/../includes/util.php';
$data = load_data();
$hospId = $data['hospitals'][0]['id'] ?? '';
if ($hospId === '') {
    $hospId = 'hosp_1';
}
$view = isset($_GET['view']) ? (string)$_GET['view'] : 'dashboard';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_doctor') {
        $name = str('name');
        $hospitalId = str('hospital_id');
        $password = str('password');
        if ($name !== '' && $hospitalId !== '' && $password !== '') {
            add_doctor($data, $name, $hospitalId, $password);
            save_data($data);
        }
        redirect('admin_dashboard.php?view=list_doctors');
    } elseif ($_POST['action'] === 'add_attendant') {
        $name = str('name');
        $hospitalId = str('hospital_id');
        $password = str('password');
        if ($name !== '' && $hospitalId !== '' && $password !== '') {
            add_attendant($data, $name, $hospitalId, $password);
            save_data($data);
        }
        redirect('admin_dashboard.php?view=list_attendants');
    }
}
$stats = hospital_stats($data, $hospId);
$hospitals = hospitals_options($data);
$currentHospitalName = $hospitals[$hospId] ?? 'Unknown';
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>

<body>
    <header class="header">
        <h1>Admin Dashboard</h1>
    </header>
    <div class="layout">
        <aside class="sidebar">
            <div class="sidebar-section">
                <div class="sidebar-title">Hospital</div>
                <div class="sidebar-item muted"><?php echo htmlspecialchars($currentHospitalName); ?></div>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-title">Navigation</div>
                <a class="sidebar-item<?php if ($view === 'dashboard') echo ' active'; ?>"
                    href="admin_dashboard.php?view=dashboard">Dashboard</a>
                <a class="sidebar-item<?php if ($view === 'list_doctors') echo ' active'; ?>"
                    href="admin_dashboard.php?view=list_doctors">Doctors</a>
                <a class="sidebar-item<?php if ($view === 'list_attendants') echo ' active'; ?>"
                    href="admin_dashboard.php?view=list_attendants">Attendants</a>
                <a class="sidebar-item<?php if ($view === 'list_patients') echo ' active'; ?>"
                    href="admin_dashboard.php?view=list_patients">Patients</a>
                <a class="sidebar-item<?php if ($view === 'list_appointments') echo ' active'; ?>"
                    href="admin_dashboard.php?view=list_appointments">Appointments</a>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-title">Actions</div>
                <a class="sidebar-item<?php if ($view === 'add_doctor') echo ' active'; ?>"
                    href="admin_dashboard.php?view=add_doctor">Add Doctor</a>
                <a class="sidebar-item<?php if ($view === 'add_attendant') echo ' active'; ?>"
                    href="admin_dashboard.php?view=add_attendant">Add Attendant</a>
            </div>
        </aside>
        <main class="main container">
            <?php if ($view === 'dashboard'): ?>
            <section class="panel hero">
                <div class="hero-title"><?php echo htmlspecialchars($currentHospitalName); ?></div>
                <div class="hero-subtitle">Overview</div>
                <!-- <div class="hero-actions">
                    <a class="btn" href="admin_dashboard.php?view=add_doctor">Add Doctor</a>
                    <a class="btn secondary" href="admin_dashboard.php?view=add_attendant">Add Attendant</a>
                </div> -->
            </section>
            <section class="cards">
                <div class="card accent-1">
                    <div class="card-title">Doctors</div>
                    <div class="card-value"><?php echo $stats['doctors']; ?></div>
                </div>
                <div class="card accent-2">
                    <div class="card-title">Attendants</div>
                    <div class="card-value"><?php echo $stats['attendants']; ?></div>
                </div>
                <div class="card accent-3">
                    <div class="card-title">Patients</div>
                    <div class="card-value"><?php echo $stats['patients']; ?></div>
                </div>
                <div class="card accent-4">
                    <div class="card-title">Patients Today</div>
                    <div class="card-value"><?php echo count_patients_today($data, $hospId); ?></div>
                </div>
                <div class="card accent-5">
                    <div class="card-title">Patients This Week</div>
                    <div class="card-value"><?php echo count_patients_this_week($data, $hospId); ?></div>
                </div>
                <div class="card accent-6">
                    <div class="card-title">Appointments</div>
                    <div class="card-value"><?php echo $stats['appointments']; ?></div>
                </div>
            </section>
            <?php elseif ($view === 'list_doctors'): ?>
            <section class="panel">
                <h2>Doctors</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['doctors_list'] as $d): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($d['name']); ?></td>
                            <td class="muted"><?php echo htmlspecialchars($d['id']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (count($stats['doctors_list']) === 0): ?>
                        <tr>
                            <td colspan="2" class="muted center">No doctors yet</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
            <?php elseif ($view === 'list_attendants'): ?>
            <section class="panel">
                <h2>Attendants</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['attendants_list'] as $a): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($a['name']); ?></td>
                            <td class="muted"><?php echo htmlspecialchars($a['id']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (count($stats['attendants_list']) === 0): ?>
                        <tr>
                            <td colspan="2" class="muted center">No attendants yet</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
            <?php elseif ($view === 'list_patients'): ?>
            <section class="panel">
                <h2>Patients</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['patients_list'] as $p): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($p['name'] ?? ''); ?></td>
                            <td class="muted"><?php echo htmlspecialchars($p['id'] ?? ''); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (count($stats['patients_list']) === 0): ?>
                        <tr>
                            <td colspan="2" class="muted center">No patients yet</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
            <?php elseif ($view === 'list_appointments'): ?>
            <section class="panel">
                <h2>Appointments</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['appointments'] as $ap): ?>
                        <?php if (($ap['hospital_id'] ?? '') !== $hospId) continue; ?>
                        <tr>
                            <td><?php echo htmlspecialchars($ap['patient_name'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($ap['doctor_name'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($ap['date'] ?? ''); ?></td>
                            <td class="muted"><?php echo htmlspecialchars($ap['status'] ?? ''); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (count(array_filter($data['appointments'], fn($ap) => ($ap['hospital_id'] ?? '') === $hospId)) === 0): ?>
                        <tr>
                            <td colspan="4" class="muted center">No appointments yet</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
            <?php elseif ($view === 'add_doctor'): ?>
            <section class="panel">
                <h2>Add Doctor</h2>
                <form method="post" class="form">
                    <input type="hidden" name="action" value="add_doctor">
                    <input type="hidden" name="hospital_id" value="<?php echo htmlspecialchars($hospId); ?>">
                    <div class="form-row">
                        <label>Name</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-row">
                        <label>Password</label>
                        <input type="password" name="password" required>
                    </div>
                    <div class="form-row">
                        <button type="submit" class="btn">Add Doctor</button>
                    </div>
                </form>
            </section>
            <?php elseif ($view === 'add_attendant'): ?>
            <section class="panel">
                <h2>Add Attendant</h2>
                <form method="post" class="form">
                    <input type="hidden" name="action" value="add_attendant">
                    <input type="hidden" name="hospital_id" value="<?php echo htmlspecialchars($hospId); ?>">
                    <div class="form-row">
                        <label>Name</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-row">
                        <label>Password</label>
                        <input type="password" name="password" required>
                    </div>
                    <div class="form-row">
                        <button type="submit" class="btn">Add Attendant</button>
                    </div>
                </form>
            </section>
            <?php endif; ?>
        </main>
    </div>
</body>

</html>
