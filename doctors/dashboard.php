<?php

declare(strict_types=1);
require __DIR__ . '/../includes/util.php';
$data = load_data();
$doctorId = isset($_GET['doctor_id']) ? (string)$_GET['doctor_id'] : ($data['doctors'][0]['id'] ?? '');
$patientId = isset($_GET['patient_id']) ? (string)$_GET['patient_id'] : '';
$view = isset($_GET['view']) ? (string)$_GET['view'] : 'dashboard';
$doctor = null;
foreach ($data['doctors'] as $d) {
    if (($d['id'] ?? '') === $doctorId) {
        $doctor = $d;
        break;
    }
}
$doctorName = $doctor ? ($doctor['name'] ?? 'Doctor') : 'Doctor';
$statsToday = $doctorId ? doctor_today_stats($data, $doctorId) : ['total' => 0, 'done' => 0, 'remaining' => 0];
$upcoming = $doctorId ? upcoming_appointments_for_doctor($data, $doctorId) : [];
if (!$patientId) {
    $activeAp = first_active_appointment_for_doctor($data, $doctorId);
    if ($activeAp) {
        $patientId = $activeAp['patient_id'] ?? '';
    } elseif (count($upcoming) > 0) {
        $patientId = $upcoming[0]['patient_id'] ?? '';
    }
}
$activePatient = $patientId ? get_patient_by_id($data, $patientId) : null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_prescription') {
        $text = str('text');
        if ($doctorId && $patientId && $text !== '') {
            add_prescription($data, $doctorId, $patientId, $text);
            save_data($data);
        }
        redirect('dashboard.php?view=active&doctor_id=' . urlencode($doctorId) . '&patient_id=' . urlencode($patientId));
    } elseif ($_POST['action'] === 'add_note') {
        $text = str('text');
        if ($doctorId && $patientId && $text !== '') {
            add_note($data, $doctorId, $patientId, $text);
            save_data($data);
        }
        redirect('dashboard.php?view=active&doctor_id=' . urlencode($doctorId) . '&patient_id=' . urlencode($patientId));
    } elseif ($_POST['action'] === 'give_test') {
        $name = str('name');
        $remarks = str('remarks');
        if ($doctorId && $patientId && $name !== '') {
            add_test_order($data, $doctorId, $patientId, $name, $remarks);
            save_data($data);
        }
        redirect('dashboard.php?view=active&doctor_id=' . urlencode($doctorId) . '&patient_id=' . urlencode($patientId));
    } elseif ($_POST['action'] === 'add_test_report') {
        $name = str('name');
        $report = str('report');
        if ($doctorId && $patientId && $name !== '' && $report !== '') {
            add_test_report($data, $doctorId, $patientId, $name, $report);
            save_data($data);
        }
        redirect('dashboard.php?view=active&doctor_id=' . urlencode($doctorId) . '&patient_id=' . urlencode($patientId));
    }
}
$hasActive = $activePatient !== null;
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Doctor Dashboard</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>

<body>
    <header class="header">
        <h1>Doctor Dashboard</h1>
    </header>
    <div class="layout">
        <aside class="sidebar">
            <div class="sidebar-section">
                <div class="sidebar-title">Doctor</div>
                <div class="sidebar-item muted"><?php echo htmlspecialchars($doctorName); ?></div>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-title">Navigation</div>
                <a class="sidebar-item<?php if ($view === 'dashboard') echo ' active'; ?>"
                    href="dashboard.php?view=dashboard&doctor_id=<?php echo urlencode($doctorId); ?>">Dashboard</a>
                <a class="sidebar-item<?php if ($view === 'active') echo ' active'; ?>"
                    href="dashboard.php?view=active&doctor_id=<?php echo urlencode($doctorId); ?>&patient_id=<?php echo urlencode($patientId); ?>">Active
                    Patient</a>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-title">Actions</div>
                <a class="sidebar-item<?php if ($view === 'add_prescription') echo ' active'; ?><?php if (!$hasActive) echo ' disabled'; ?>"
                    href="<?php echo $hasActive ? 'dashboard.php?view=add_prescription&doctor_id=' . urlencode($doctorId) . '&patient_id=' . urlencode($patientId) : 'dashboard.php?view=active&doctor_id=' . urlencode($doctorId); ?>">Add
                    Prescription</a>
                <a class="sidebar-item<?php if ($view === 'prev_prescriptions') echo ' active'; ?><?php if (!$hasActive) echo ' disabled'; ?>"
                    href="<?php echo $hasActive ? 'dashboard.php?view=prev_prescriptions&doctor_id=' . urlencode($doctorId) . '&patient_id=' . urlencode($patientId) : 'dashboard.php?view=active&doctor_id=' . urlencode($doctorId); ?>">Previous
                    Prescriptions</a>
                <a class="sidebar-item<?php if ($view === 'give_test') echo ' active'; ?><?php if (!$hasActive) echo ' disabled'; ?>"
                    href="<?php echo $hasActive ? 'dashboard.php?view=give_test&doctor_id=' . urlencode($doctorId) . '&patient_id=' . urlencode($patientId) : 'dashboard.php?view=active&doctor_id=' . urlencode($doctorId); ?>">Give
                    Test</a>
                <a class="sidebar-item<?php if ($view === 'test_reports') echo ' active'; ?><?php if (!$hasActive) echo ' disabled'; ?>"
                    href="<?php echo $hasActive ? 'dashboard.php?view=test_reports&doctor_id=' . urlencode($doctorId) . '&patient_id=' . urlencode($patientId) : 'dashboard.php?view=active&doctor_id=' . urlencode($doctorId); ?>">Test
                    Reports</a>
                <a class="sidebar-item<?php if ($view === 'add_note') echo ' active'; ?><?php if (!$hasActive) echo ' disabled'; ?>"
                    href="<?php echo $hasActive ? 'dashboard.php?view=add_note&doctor_id=' . urlencode($doctorId) . '&patient_id=' . urlencode($patientId) : 'dashboard.php?view=active&doctor_id=' . urlencode($doctorId); ?>">Add
                    Remarks Notes</a>
            </div>
        </aside>
        <main class="main container">
            <div class="tabs">
                <a class="tab<?php if ($view === 'dashboard') echo ' active'; ?>"
                    href="dashboard.php?view=dashboard&doctor_id=<?php echo urlencode($doctorId); ?>">Dashboard</a>
                <a class="tab<?php if ($view === 'active') echo ' active'; ?>"
                    href="dashboard.php?view=active&doctor_id=<?php echo urlencode($doctorId); ?>&patient_id=<?php echo urlencode($patientId); ?>">Active
                    Patient</a>
            </div>
            <?php if ($view === 'dashboard'): ?>
            <section class="cards">
                <div class="card accent-1">
                    <div class="card-title">Today's Appointments</div>
                    <div class="card-value"><?php echo $statsToday['total']; ?></div>
                </div>
                <div class="card accent-2">
                    <div class="card-title">Done Today</div>
                    <div class="card-value"><?php echo $statsToday['done']; ?></div>
                </div>
                <div class="card accent-3">
                    <div class="card-title">Remaining Today</div>
                    <div class="card-value"><?php echo $statsToday['remaining']; ?></div>
                </div>
            </section>
            <section class="panel">
                <h2>Upcoming Patients</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($upcoming as $ap): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($ap['patient_name'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($ap['date'] ?? ''); ?></td>
                            <td class="muted"><?php echo htmlspecialchars($ap['status'] ?? ''); ?></td>
                            <td><a class="btn"
                                    href="dashboard.php?view=active&doctor_id=<?php echo urlencode($doctorId); ?>&patient_id=<?php echo urlencode($ap['patient_id'] ?? ''); ?>">Open</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (count($upcoming) === 0): ?>
                        <tr>
                            <td colspan="4" class="muted center">No upcoming patients</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
            <?php elseif ($view === 'active'): ?>
            <section class="panel">
                <h2>Active Patient</h2>
                <?php if ($activePatient): ?>
                <div class="form">
                    <div class="form-row"><label>Patient</label>
                        <div><?php echo htmlspecialchars($activePatient['name'] ?? ''); ?></div>
                    </div>
                    <div class="form-row"><label>ID</label>
                        <div class="muted"><?php echo htmlspecialchars($activePatient['id'] ?? ''); ?></div>
                    </div>
                </div>
                <div class="cards">
                    <div class="card">
                        <div class="card-title">Prescriptions</div>
                        <div class="card-value">
                            <?php echo count(array_filter($data['prescriptions'], fn($rx) => ($rx['patient_id'] ?? '') === $patientId)); ?>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-title">Tests</div>
                        <div class="card-value">
                            <?php echo count(array_filter($data['tests'], fn($t) => ($t['patient_id'] ?? '') === $patientId)); ?>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-title">Reports</div>
                        <div class="card-value">
                            <?php echo count(array_filter($data['test_reports'], fn($r) => ($r['patient_id'] ?? '') === $patientId)); ?>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-title">Notes</div>
                        <div class="card-value">
                            <?php echo count(array_filter($data['notes'], fn($n) => ($n['patient_id'] ?? '') === $patientId)); ?>
                        </div>
                    </div>
                </div>
                <section class="panel">
                    <h2>Recent Activity</h2>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Title</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['prescriptions'] as $rx): ?>
                            <?php if (($rx['patient_id'] ?? '') !== $patientId) continue; ?>
                            <tr>
                                <td>Prescription</td>
                                <td><?php echo htmlspecialchars(substr($rx['text'], 0, 60)); ?></td>
                                <td class="muted"><?php echo htmlspecialchars($rx['created_at']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php foreach ($data['tests'] as $t): ?>
                            <?php if (($t['patient_id'] ?? '') !== $patientId) continue; ?>
                            <tr>
                                <td>Test</td>
                                <td><?php echo htmlspecialchars($t['name']); ?></td>
                                <td class="muted"><?php echo htmlspecialchars($t['created_at']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php foreach ($data['test_reports'] as $r): ?>
                            <?php if (($r['patient_id'] ?? '') !== $patientId) continue; ?>
                            <tr>
                                <td>Report</td>
                                <td><?php echo htmlspecialchars($r['name']); ?></td>
                                <td class="muted"><?php echo htmlspecialchars($r['created_at']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php foreach ($data['notes'] as $n): ?>
                            <?php if (($n['patient_id'] ?? '') !== $patientId) continue; ?>
                            <tr>
                                <td>Note</td>
                                <td><?php echo htmlspecialchars(substr($n['text'], 0, 60)); ?></td>
                                <td class="muted"><?php echo htmlspecialchars($n['created_at']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>
                <?php else: ?>
                <div class="form">
                    <div class="muted">No active patient selected</div>
                </div>
                <?php endif; ?>
            </section>
            <?php elseif ($view === 'add_prescription'): ?>
            <section class="panel">
                <h2>Add Prescription</h2>
                <?php if ($activePatient): ?>
                <form method="post" class="form">
                    <input type="hidden" name="action" value="add_prescription">
                    <div class="form-row"><label>Text</label><textarea name="text" rows="4" required
                            class="input"></textarea></div>
                    <div class="form-row"><button type="submit" class="btn">Save</button></div>
                </form>
                <?php else: ?><div class="form">
                    <div class="muted">Select a patient first</div>
                </div><?php endif; ?>
            </section>
            <?php elseif ($view === 'prev_prescriptions'): ?>
            <section class="panel">
                <h2>Previous Prescriptions</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Text</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['prescriptions'] as $rx): ?>
                        <?php if (($rx['patient_id'] ?? '') !== $patientId) continue; ?>
                        <tr>
                            <td><?php echo htmlspecialchars($rx['text']); ?></td>
                            <td class="muted"><?php echo htmlspecialchars($rx['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (count(array_filter($data['prescriptions'], fn($rx) => ($rx['patient_id'] ?? '') === $patientId)) === 0): ?>
                        <tr>
                            <td colspan="2" class="muted center">No prescriptions</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
            <?php elseif ($view === 'give_test'): ?>
            <section class="panel">
                <h2>Give Test</h2>
                <?php if ($activePatient): ?>
                <form method="post" class="form">
                    <input type="hidden" name="action" value="give_test">
                    <div class="form-row"><label>Test Name</label><input type="text" name="name" required></div>
                    <div class="form-row"><label>Remarks</label><textarea name="remarks" rows="3"
                            class="input"></textarea></div>
                    <div class="form-row"><button type="submit" class="btn">Order Test</button></div>
                </form>
                <?php else: ?><div class="form">
                    <div class="muted">Select a patient first</div>
                </div><?php endif; ?>
            </section>
            <?php elseif ($view === 'test_reports'): ?>
            <section class="panel">
                <h2>Test Reports</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Test</th>
                            <th>Report</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['test_reports'] as $r): ?>
                        <?php if (($r['patient_id'] ?? '') !== $patientId) continue; ?>
                        <tr>
                            <td><?php echo htmlspecialchars($r['name']); ?></td>
                            <td><?php echo htmlspecialchars($r['report']); ?></td>
                            <td class="muted"><?php echo htmlspecialchars($r['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (count(array_filter($data['test_reports'], fn($r) => ($r['patient_id'] ?? '') === $patientId)) === 0): ?>
                        <tr>
                            <td colspan="3" class="muted center">No test reports</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <section class="panel">
                    <h2>Add Test Report</h2>
                    <?php if ($activePatient): ?>
                    <form method="post" class="form">
                        <input type="hidden" name="action" value="add_test_report">
                        <div class="form-row"><label>Test Name</label><input type="text" name="name" required></div>
                        <div class="form-row"><label>Report</label><textarea name="report" rows="4" required
                                class="input"></textarea></div>
                        <div class="form-row"><button type="submit" class="btn">Save Report</button></div>
                    </form>
                    <?php else: ?><div class="form">
                        <div class="muted">Select a patient first</div>
                    </div><?php endif; ?>
                </section>
            </section>
            <?php elseif ($view === 'add_note'): ?>
            <section class="panel">
                <h2>Add Remarks Notes</h2>
                <?php if ($activePatient): ?>
                <form method="post" class="form">
                    <input type="hidden" name="action" value="add_note">
                    <div class="form-row"><label>Notes</label><textarea name="text" rows="3" required
                            class="input"></textarea></div>
                    <div class="form-row"><button type="submit" class="btn">Save Note</button></div>
                </form>
                <?php else: ?><div class="form">
                    <div class="muted">Select a patient first</div>
                </div><?php endif; ?>
            </section>
            <?php endif; ?>
        </main>
    </div>
</body>

</html>