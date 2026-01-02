<?php
declare(strict_types=1);
require __DIR__ . '/../includes/util.php';
$data = load_data();
$attendant = $data['attendants'][0] ?? ['id' => 'att_demo', 'name' => 'Attendant', 'hospital_id' => $data['hospitals'][0]['id'] ?? 'hosp_1'];
$hospitalId = $attendant['hospital_id'] ?? ($data['hospitals'][0]['id'] ?? 'hosp_1');
$view = isset($_GET['view']) ? (string)$_GET['view'] : 'dashboard';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = (string)$_POST['action'];
    if ($action === 'accept_request') {
        $appointmentId = (string)($_POST['appointment_id'] ?? '');
        $doctorId = (string)($_POST['doctor_id'] ?? '');
        $dateInput = (string)($_POST['date'] ?? '');
        $doctor = null;
        foreach ($data['doctors'] as $d) { if (($d['id'] ?? '') === $doctorId) { $doctor = $d; break; } }
        $doctorName = $doctor ? ($doctor['name'] ?? '') : '';
        $date = $dateInput !== '' ? date('Y-m-d H:i:s', strtotime($dateInput)) : date('Y-m-d H:i:s');
        accept_appointment($data, $appointmentId, $doctorId, $doctorName, $date);
        save_data($data);
        $message = 'Request accepted';
    } elseif ($action === 'reschedule') {
        $appointmentId = (string)($_POST['appointment_id'] ?? '');
        $dateInput = (string)($_POST['date'] ?? '');
        $date = $dateInput !== '' ? date('Y-m-d H:i:s', strtotime($dateInput)) : date('Y-m-d H:i:s');
        reschedule_appointment($data, $appointmentId, $date);
        save_data($data);
        $message = 'Appointment rescheduled';
    } elseif ($action === 'mark_checkin') {
        $appointmentId = (string)($_POST['appointment_id'] ?? '');
        set_appointment_status($data, $appointmentId, 'checkin');
        save_data($data);
        $message = 'Marked as check-in';
    } elseif ($action === 'mark_in_progress') {
        $appointmentId = (string)($_POST['appointment_id'] ?? '');
        set_appointment_status($data, $appointmentId, 'in_progress');
        save_data($data);
        $message = 'Marked as in progress';
    } elseif ($action === 'mark_done') {
        $appointmentId = (string)($_POST['appointment_id'] ?? '');
        set_appointment_status($data, $appointmentId, 'done');
        save_data($data);
        $message = 'Marked as done';
    }
}

$requested = list_requested($data, $hospitalId);
$upcoming = list_upcoming($data, $hospitalId);
$active = list_active($data, $hospitalId);
$todayDone = count_today_done($data, $hospitalId);
$todayAll = count_today_all($data, $hospitalId);
$requestedCount = count_requested($data, $hospitalId);
$doctors = hospital_doctors($data, $hospitalId);
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Attendant Dashboard</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <style>
        @media (max-width: 820px) {
            .layout { flex-direction: column; }
            .sidebar { width: 100%; border-right: none; border-bottom: 1px solid #1f2937; }
        }
        .badge { display: inline-block; padding: 4px 8px; background: #1f2937; border: 1px solid #374151; border-radius: 999px; font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>
    <header class="header">
        <h1>Attendant Dashboard</h1>
    </header>
    <div class="layout">
        <aside class="sidebar">
            <div class="sidebar-section">
                <div class="sidebar-title">Today</div>
                <div class="sidebar-item">Done Today <span class="badge"><?php echo $todayDone; ?></span></div>
                <div class="sidebar-item">All Today <span class="badge"><?php echo $todayAll; ?></span></div>
                <div class="sidebar-item">Requested <span class="badge"><?php echo $requestedCount; ?></span></div>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-title">Views</div>
                <a class="sidebar-item<?php if ($view === 'dashboard') echo ' active'; ?>" href="dashboard.php?view=dashboard">Dashboard</a>
                <a class="sidebar-item<?php if ($view === 'requested') echo ' active'; ?>" href="dashboard.php?view=requested">Requested</a>
                <a class="sidebar-item<?php if ($view === 'today') echo ' active'; ?>" href="dashboard.php?view=today">Today</a>
                <a class="sidebar-item<?php if ($view === 'done_today') echo ' active'; ?>" href="dashboard.php?view=done_today">Done Today</a>
            </div>
        </aside>
        <main class="main container">
            <?php if ($message): ?>
                <section class="panel"><div class="form"><div class="badge"><?php echo htmlspecialchars($message); ?></div></div></section>
            <?php endif; ?>
            <?php if ($view === 'dashboard'): ?>
                <section class="panel">
                    <h2>Requested Patients</h2>
                    <table class="table">
                        <thead><tr><th>Patient</th><th>Desired Time</th><th>Doctor</th><th>New Time</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($requested as $ap): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($ap['patient_name'] ?? ''); ?></td>
                                    <td class="muted"><?php echo htmlspecialchars($ap['date'] ?? ''); ?></td>
                                    <td>
                                        <form method="post" style="display:flex; gap:8px; align-items:center;">
                                            <input type="hidden" name="appointment_id" value="<?php echo htmlspecialchars($ap['id'] ?? ''); ?>">
                                            <select name="doctor_id">
                                                <?php foreach ($doctors as $doc): ?>
                                                    <option value="<?php echo htmlspecialchars($doc['id']); ?>"><?php echo htmlspecialchars($doc['name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="datetime-local" name="date" value="">
                                            <button class="btn" type="submit" name="action" value="accept_request">Accept</button>
                                            <button class="btn secondary" type="submit" name="action" value="reschedule">Reschedule</button>
                                        </form>
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (count($requested) === 0): ?>
                                <tr><td colspan="5" class="muted center">No requested patients</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </section>
                <section class="panel">
                    <h2>Active Patients</h2>
                    <table class="table">
                        <thead><tr><th>Patient</th><th>Doctor</th><th>Time</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($active as $ap): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($ap['patient_name'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($ap['doctor_name'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($ap['date'] ?? ''); ?></td>
                                    <td class="muted"><?php echo htmlspecialchars($ap['status'] ?? ''); ?></td>
                                    <td>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="appointment_id" value="<?php echo htmlspecialchars($ap['id'] ?? ''); ?>">
                                            <button class="btn" type="submit" name="action" value="mark_done">Mark Done</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (count($active) === 0): ?>
                                <tr><td colspan="5" class="muted center">No active patients</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </section>
                <section class="panel">
                    <h2>Upcoming Patients</h2>
                    <table class="table">
                        <thead><tr><th>Patient</th><th>Doctor</th><th>Time</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($upcoming as $ap): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($ap['patient_name'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($ap['doctor_name'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($ap['date'] ?? ''); ?></td>
                                    <td class="muted"><?php echo htmlspecialchars($ap['status'] ?? ''); ?></td>
                                    <td>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="appointment_id" value="<?php echo htmlspecialchars($ap['id'] ?? ''); ?>">
                                            <button class="btn secondary" type="submit" name="action" value="mark_checkin">Mark Check-in</button>
                                            <button class="btn" type="submit" name="action" value="mark_in_progress">Mark In Progress</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (count($upcoming) === 0): ?>
                                <tr><td colspan="5" class="muted center">No upcoming patients</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </section>
            <?php elseif ($view === 'requested'): ?>
                <section class="panel">
                    <h2>Requested Patients</h2>
                    <table class="table">
                        <thead><tr><th>Patient</th><th>Desired Time</th><th>Doctor</th><th>New Time</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($requested as $ap): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($ap['patient_name'] ?? ''); ?></td>
                                    <td class="muted"><?php echo htmlspecialchars($ap['date'] ?? ''); ?></td>
                                    <td>
                                        <form method="post" style="display:flex; gap:8px; align-items:center;">
                                            <input type="hidden" name="appointment_id" value="<?php echo htmlspecialchars($ap['id'] ?? ''); ?>">
                                            <select name="doctor_id">
                                                <?php foreach ($doctors as $doc): ?>
                                                    <option value="<?php echo htmlspecialchars($doc['id']); ?>"><?php echo htmlspecialchars($doc['name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="datetime-local" name="date" value="">
                                            <button class="btn" type="submit" name="action" value="accept_request">Accept</button>
                                            <button class="btn secondary" type="submit" name="action" value="reschedule">Reschedule</button>
                                        </form>
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (count($requested) === 0): ?>
                                <tr><td colspan="5" class="muted center">No requested patients</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </section>
            <?php elseif ($view === 'today'): ?>
                <section class="panel">
                    <h2>All Patients Today</h2>
                    <table class="table">
                        <thead><tr><th>Patient</th><th>Doctor</th><th>Time</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php foreach ($data['appointments'] as $ap): ?>
                                <?php if (($ap['hospital_id'] ?? '') !== $hospitalId) continue; ?>
                                <?php if (!is_today_ts($ap['date'] ?? null)) continue; ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($ap['patient_name'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($ap['doctor_name'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($ap['date'] ?? ''); ?></td>
                                    <td class="muted"><?php echo htmlspecialchars($ap['status'] ?? ''); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>
            <?php elseif ($view === 'done_today'): ?>
                <section class="panel">
                    <h2>Done Patients Today</h2>
                    <table class="table">
                        <thead><tr><th>Patient</th><th>Doctor</th><th>Time</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php foreach ($data['appointments'] as $ap): ?>
                                <?php if (($ap['hospital_id'] ?? '') !== $hospitalId) continue; ?>
                                <?php if (($ap['status'] ?? '') !== 'done') continue; ?>
                                <?php if (!is_today_ts($ap['date'] ?? null)) continue; ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($ap['patient_name'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($ap['doctor_name'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($ap['date'] ?? ''); ?></td>
                                    <td class="muted"><?php echo htmlspecialchars($ap['status'] ?? ''); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
