<?php
declare(strict_types=1);
require __DIR__ . '/../includes/util.php';
$data = load_data();
$view = isset($_GET['view']) ? (string)$_GET['view'] : 'request';
$patientId = isset($_GET['patient_id']) ? (string)$_GET['patient_id'] : '';
$hospitals = hospitals_options($data);
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = (string)$_POST['action'];
    if ($action === 'set_patient') {
        $name = str('name');
        $hospitalId = str('hospital_id');
        if ($name !== '' && $hospitalId !== '') {
            $existing = find_patient_by_name($data, $name, $hospitalId);
            $patient = $existing ?: add_patient($data, $name, $hospitalId);
            save_data($data);
            redirect('dashboard.php?view=request&patient_id=' . urlencode($patient['id']));
        }
    } elseif ($action === 'request_appointment') {
        $name = str('name');
        $hospitalId = str('hospital_id');
        $dateInput = (string)($_POST['date'] ?? '');
        $desiredDate = $dateInput !== '' ? date('Y-m-d H:i:s', strtotime($dateInput)) : date('Y-m-d H:i:s');
        if ($patientId === '' && $name !== '' && $hospitalId !== '') {
            $existing = find_patient_by_name($data, $name, $hospitalId);
            $patient = $existing ?: add_patient($data, $name, $hospitalId);
            $patientId = $patient['id'];
        }
        if ($patientId !== '') {
            $p = get_patient_by_id($data, $patientId);
            if ($p) {
                add_appointment_request($data, $p['hospital_id'], $patientId, $p['name'], $desiredDate);
                save_data($data);
                $message = 'Appointment requested';
                redirect('dashboard.php?view=appointments&patient_id=' . urlencode($patientId));
            }
        }
    }
}
$patient = $patientId ? get_patient_by_id($data, $patientId) : null;
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Patient Portal</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <style>
        .mobile-wrap { max-width: 680px; margin: 0 auto; padding: 12px; }
        .tabs { position: sticky; top: 56px; z-index: 5; }
        @media (max-width: 740px) {
            .header { padding: 12px 16px; }
            .container { padding: 0; }
            .panel { margin-bottom: 16px; }
            .cards { grid-template-columns: repeat(2, 1fr); }
            .table { display: block; overflow-x: auto; }
        }
    </style>
</head>
<body>
    <header class="header">
        <h1>Patient Portal</h1>
    </header>
    <main class="container mobile-wrap">
        <div class="tabs">
            <a class="tab<?php if ($view === 'request') echo ' active'; ?>" href="dashboard.php?view=request<?php if ($patientId) echo '&patient_id=' . urlencode($patientId); ?>">Request</a>
            <a class="tab<?php if ($view === 'appointments') echo ' active'; ?>" href="dashboard.php?view=appointments<?php if ($patientId) echo '&patient_id=' . urlencode($patientId); ?>">Appointments</a>
            <a class="tab<?php if ($view === 'prescriptions') echo ' active'; ?>" href="dashboard.php?view=prescriptions<?php if ($patientId) echo '&patient_id=' . urlencode($patientId); ?>">Prescriptions</a>
            <a class="tab<?php if ($view === 'reports') echo ' active'; ?>" href="dashboard.php?view=reports<?php if ($patientId) echo '&patient_id=' . urlencode($patientId); ?>">Reports</a>
            <a class="tab<?php if ($view === 'notes') echo ' active'; ?>" href="dashboard.php?view=notes<?php if ($patientId) echo '&patient_id=' . urlencode($patientId); ?>">Remarks</a>
        </div>
        <?php if (!$patient && $view !== 'request'): ?>
            <section class="panel">
                <h2>Identify</h2>
                <form method="post" class="form">
                    <input type="hidden" name="action" value="set_patient">
                    <div class="form-row"><label>Name</label><input type="text" name="name" required></div>
                    <div class="form-row">
                        <label>Hospital</label>
                        <select name="hospital_id" required>
                            <?php foreach ($hospitals as $hid => $hname): ?>
                                <option value="<?php echo htmlspecialchars($hid); ?>"><?php echo htmlspecialchars($hname); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-row"><button type="submit" class="btn">Continue</button></div>
                </form>
            </section>
        <?php endif; ?>
        <?php if ($view === 'request'): ?>
            <section class="panel">
                <h2>Request Appointment</h2>
                <form method="post" class="form">
                    <input type="hidden" name="action" value="request_appointment">
                    <?php if (!$patient): ?>
                        <div class="form-row"><label>Name</label><input type="text" name="name" required></div>
                        <div class="form-row">
                            <label>Hospital</label>
                            <select name="hospital_id" required>
                                <?php foreach ($hospitals as $hid => $hname): ?>
                                    <option value="<?php echo htmlspecialchars($hid); ?>"><?php echo htmlspecialchars($hname); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <div class="form-row"><label>Patient</label><div><?php echo htmlspecialchars($patient['name']); ?></div></div>
                        <input type="hidden" name="hospital_id" value="<?php echo htmlspecialchars($patient['hospital_id']); ?>">
                        <input type="hidden" name="name" value="<?php echo htmlspecialchars($patient['name']); ?>">
                    <?php endif; ?>
                    <div class="form-row"><label>Preferred Time</label><input type="datetime-local" name="date"></div>
                    <div class="form-row"><button type="submit" class="btn">Request</button></div>
                </form>
            </section>
        <?php elseif ($view === 'appointments' && $patient): ?>
            <section class="panel">
                <h2>Appointments</h2>
                <table class="table">
                    <thead><tr><th>Doctor</th><th>Time</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach ($data['appointments'] as $ap): ?>
                            <?php if (($ap['patient_id'] ?? '') !== $patientId) continue; ?>
                            <tr>
                                <td><?php echo htmlspecialchars($ap['doctor_name'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($ap['date'] ?? ''); ?></td>
                                <td class="muted"><?php echo htmlspecialchars($ap['status'] ?? ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        <?php elseif ($view === 'prescriptions' && $patient): ?>
            <section class="panel">
                <h2>Prescriptions</h2>
                <table class="table">
                    <thead><tr><th>Text</th><th>Date</th></tr></thead>
                    <tbody>
                        <?php foreach ($data['prescriptions'] as $rx): ?>
                            <?php if (($rx['patient_id'] ?? '') !== $patientId) continue; ?>
                            <tr><td><?php echo htmlspecialchars($rx['text']); ?></td><td class="muted"><?php echo htmlspecialchars($rx['created_at']); ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        <?php elseif ($view === 'reports' && $patient): ?>
            <section class="panel">
                <h2>Reports</h2>
                <table class="table">
                    <thead><tr><th>Test</th><th>Report</th><th>Date</th></tr></thead>
                    <tbody>
                        <?php foreach ($data['test_reports'] as $r): ?>
                            <?php if (($r['patient_id'] ?? '') !== $patientId) continue; ?>
                            <tr><td><?php echo htmlspecialchars($r['name']); ?></td><td><?php echo htmlspecialchars($r['report']); ?></td><td class="muted"><?php echo htmlspecialchars($r['created_at']); ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        <?php elseif ($view === 'notes' && $patient): ?>
            <section class="panel">
                <h2>Remarks</h2>
                <table class="table">
                    <thead><tr><th>Text</th><th>Date</th></tr></thead>
                    <tbody>
                        <?php foreach ($data['notes'] as $n): ?>
                            <?php if (($n['patient_id'] ?? '') !== $patientId) continue; ?>
                            <tr><td><?php echo htmlspecialchars($n['text']); ?></td><td class="muted"><?php echo htmlspecialchars($n['created_at']); ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
