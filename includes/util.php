<?php

declare(strict_types=1);

function data_file_path(): string
{
    return __DIR__ . '/../data/store.json';
}

function load_data(): array
{
    $path = data_file_path();
    if (!file_exists($path)) {
        $initial = [
            'hospitals' => [['id' => 'hosp_1', 'name' => 'General Hospital']],
            'admins' => [],
            'doctors' => [],
            'attendants' => [],
            'patients' => [],
            'appointments' => [],
        ];
        save_data($initial);
        return $initial;
    }
    $raw = file_get_contents($path);
    $data = json_decode($raw ?: '[]', true);
    if (!is_array($data)) {
        $data = [];
    }
    foreach (['hospitals', 'admins', 'doctors', 'attendants', 'patients', 'appointments', 'prescriptions', 'tests', 'test_reports', 'notes'] as $k) {
        if (!array_key_exists($k, $data) || !is_array($data[$k])) {
            $data[$k] = [];
        }
    }
    return $data;
}

function save_data(array $data): void
{
    $path = data_file_path();
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    $tmp = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    $fp = fopen($path, 'c+');
    if ($fp === false) {
        throw new RuntimeException('Unable to open data file');
    }
    if (!flock($fp, LOCK_EX)) {
        fclose($fp);
        throw new RuntimeException('Unable to lock data file');
    }
    ftruncate($fp, 0);
    fwrite($fp, $tmp);
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
}

function next_id(string $prefix): string
{
    return $prefix . '_' . bin2hex(random_bytes(6));
}

function hash_password(string $password): string
{
    return password_hash($password, PASSWORD_DEFAULT);
}

function verify_password(?string $hash, string $password): bool
{
    return $hash ? password_verify($password, $hash) : false;
}

function str($key): string
{
    return isset($_POST[$key]) ? trim((string)$_POST[$key]) : '';
}

function add_admin(array &$data, string $name, string $email, ?string $password = null): array
{
    $id = next_id('admin');
    $admin = ['id' => $id, 'name' => $name, 'email' => $email];
    if ($password !== null && $password !== '') {
        $admin['password'] = hash_password($password);
    }
    $data['admins'][] = $admin;
    return $admin;
}

function add_doctor(array &$data, string $name, string $hospitalId, ?string $password = null): array
{
    $id = next_id('doc');
    $doctor = ['id' => $id, 'name' => $name, 'hospital_id' => $hospitalId];
    if ($password !== null && $password !== '') {
        $doctor['password'] = hash_password($password);
    }
    $data['doctors'][] = $doctor;
    return $doctor;
}

function add_attendant(array &$data, string $name, string $hospitalId, ?string $password = null): array
{
    $id = next_id('att');
    $attendant = ['id' => $id, 'name' => $name, 'hospital_id' => $hospitalId];
    if ($password !== null && $password !== '') {
        $attendant['password'] = hash_password($password);
    }
    $data['attendants'][] = $attendant;
    return $attendant;
}

function add_patient(array &$data, string $name, string $hospitalId, string $email, string $password): array
{
    $id = next_id('pat');
    $patient = [
        'id' => $id,
        'name' => $name,
        'hospital_id' => $hospitalId,
        'email' => $email,
        'password' => hash_password($password),
        'created_at' => date('Y-m-d H:i:s'),
    ];
    $data['patients'][] = $patient;
    return $patient;
}

function find_patient_by_email(array $data, string $email): ?array
{
    foreach ($data['patients'] as $p) {
        if (strcasecmp($p['email'] ?? '', $email) === 0) return $p;
    }
    return null;
}

function find_patient_by_email_password(array $data, string $email, string $password): ?array
{
    foreach ($data['patients'] as $p) {
        if (strcasecmp($p['email'] ?? '', $email) === 0 && verify_password($p['password'] ?? null, $password)) {
            return $p;
        }
    }
    return null;
}

function find_doctor_by_name(array $data, string $name, string $hospitalId): ?array
{
    foreach ($data['doctors'] as $d) {
        if (strcasecmp($d['name'] ?? '', $name) === 0 && ($d['hospital_id'] ?? '') === $hospitalId) {
            return $d;
        }
    }
    return null;
}

function find_attendant_by_name(array $data, string $name, string $hospitalId): ?array
{
    foreach ($data['attendants'] as $a) {
        if (strcasecmp($a['name'] ?? '', $name) === 0 && ($a['hospital_id'] ?? '') === $hospitalId) {
            return $a;
        }
    }
    return null;
}

function find_admin_by_email(array $data, string $email): ?array
{
    foreach ($data['admins'] as $a) {
        if (strcasecmp($a['email'] ?? '', $email) === 0) {
            return $a;
        }
    }
    return null;
}

function find_admin_by_id_password(array $data, string $id, string $password): ?array
{
    foreach ($data['admins'] as $a) {
        if (($a['id'] ?? '') === $id && verify_password($a['password'] ?? null, $password)) {
            return $a;
        }
    }
    return null;
}

function get_doctor_by_id(array $data, string $id): ?array
{
    foreach ($data['doctors'] as $d) {
        if (($d['id'] ?? '') === $id) return $d;
    }
    return null;
}

function find_doctor_by_id_password(array $data, string $id, string $password): ?array
{
    foreach ($data['doctors'] as $d) {
        if (($d['id'] ?? '') === $id && verify_password($d['password'] ?? null, $password)) {
            return $d;
        }
    }
    return null;
}

function get_attendant_by_id(array $data, string $id): ?array
{
    foreach ($data['attendants'] as $a) {
        if (($a['id'] ?? '') === $id) return $a;
    }
    return null;
}

function find_attendant_by_id_password(array $data, string $id, string $password): ?array
{
    foreach ($data['attendants'] as $a) {
        if (($a['id'] ?? '') === $id && verify_password($a['password'] ?? null, $password)) {
            return $a;
        }
    }
    return null;
}

function get_admin_by_id(array $data, string $id): ?array
{
    foreach ($data['admins'] as $a) {
        if (($a['id'] ?? '') === $id) return $a;
    }
    return null;
}

function global_stats(array $data): array
{
    return [
        'hospitals' => count($data['hospitals']),
        'admins' => count($data['admins']),
        'doctors' => count($data['doctors']),
        'attendants' => count($data['attendants']),
        'patients' => count($data['patients']),
        'appointments' => count($data['appointments']),
    ];
}

function hospital_stats(array $data, string $hospitalId): array
{
    $doctors = array_values(array_filter($data['doctors'], fn($d) => ($d['hospital_id'] ?? '') === $hospitalId));
    $attendants = array_values(array_filter($data['attendants'], fn($a) => ($a['hospital_id'] ?? '') === $hospitalId));
    $patients = array_values(array_filter($data['patients'], fn($p) => ($p['hospital_id'] ?? '') === $hospitalId));
    $appointments = array_values(array_filter($data['appointments'], fn($ap) => ($ap['hospital_id'] ?? '') === $hospitalId));
    return [
        'doctors' => count($doctors),
        'attendants' => count($attendants),
        'patients' => count($patients),
        'appointments' => count($appointments),
        'doctors_list' => $doctors,
        'attendants_list' => $attendants,
        'patients_list' => $patients,
    ];
}

function hospitals_options(array $data): array
{
    $opts = [];
    foreach ($data['hospitals'] as $h) {
        $opts[$h['id']] = $h['name'];
    }
    return $opts;
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function is_today_ts(?string $createdAt): bool
{
    if (!$createdAt) return false;
    $ts = strtotime($createdAt);
    if ($ts === false) return false;
    $start = strtotime('today 00:00:00');
    $end = strtotime('today 23:59:59');
    return $ts >= $start && $ts <= $end;
}

function in_this_week_ts(?string $createdAt): bool
{
    if (!$createdAt) return false;
    $ts = strtotime($createdAt);
    if ($ts === false) return false;
    $dow = (int)date('N'); // 1 (Mon) .. 7 (Sun)
    $startOfWeek = strtotime('-' . ($dow - 1) . ' days 00:00:00');
    $endOfWeek = strtotime('+' . (7 - $dow) . ' days 23:59:59');
    return $ts >= $startOfWeek && $ts <= $endOfWeek;
}

function count_patients_today(array $data, string $hospitalId): int
{
    $n = 0;
    foreach ($data['patients'] as $p) {
        if (($p['hospital_id'] ?? '') !== $hospitalId) continue;
        if (is_today_ts($p['created_at'] ?? null)) $n++;
    }
    return $n;
}

function count_patients_this_week(array $data, string $hospitalId): int
{
    $n = 0;
    foreach ($data['patients'] as $p) {
        if (($p['hospital_id'] ?? '') !== $hospitalId) continue;
        if (in_this_week_ts($p['created_at'] ?? null)) $n++;
    }
    return $n;
}

function hospital_doctors(array $data, string $hospitalId): array
{
    return array_values(array_filter($data['doctors'], fn($d) => ($d['hospital_id'] ?? '') === $hospitalId));
}

function add_appointment_request(array &$data, string $hospitalId, string $patientId, string $patientName, ?string $desiredDate = null): array
{
    $id = next_id('appt');
    $ap = [
        'id' => $id,
        'hospital_id' => $hospitalId,
        'patient_id' => $patientId,
        'patient_name' => $patientName,
        'doctor_id' => null,
        'doctor_name' => null,
        'date' => $desiredDate ?: date('Y-m-d H:i:s'),
        'status' => 'requested',
        'created_at' => date('Y-m-d H:i:s'),
    ];
    $data['appointments'][] = $ap;
    return $ap;
}

function find_appointment_index(array $data, string $appointmentId): ?int
{
    foreach ($data['appointments'] as $i => $ap) {
        if (($ap['id'] ?? '') === $appointmentId) return $i;
    }
    return null;
}

function reschedule_appointment(array &$data, string $appointmentId, string $newDate): void
{
    $i = find_appointment_index($data, $appointmentId);
    if ($i === null) return;
    $data['appointments'][$i]['date'] = $newDate;
}

function accept_appointment(array &$data, string $appointmentId, string $doctorId, string $doctorName, string $newDate): void
{
    $i = find_appointment_index($data, $appointmentId);
    if ($i === null) return;
    $data['appointments'][$i]['doctor_id'] = $doctorId;
    $data['appointments'][$i]['doctor_name'] = $doctorName;
    $data['appointments'][$i]['date'] = $newDate;
    $data['appointments'][$i]['status'] = 'confirmed';
}

function set_appointment_status(array &$data, string $appointmentId, string $status): void
{
    $i = find_appointment_index($data, $appointmentId);
    if ($i === null) return;
    $data['appointments'][$i]['status'] = $status;
}

function list_requested(array $data, string $hospitalId): array
{
    $req = array_filter($data['appointments'], fn($ap) => ($ap['hospital_id'] ?? '') === $hospitalId && ($ap['status'] ?? '') === 'requested');
    usort($req, fn($a, $b) => strcmp($a['created_at'] ?? '', $b['created_at'] ?? ''));
    return array_values($req);
}

function list_upcoming(array $data, string $hospitalId): array
{
    $now = time();
    $up = array_filter($data['appointments'], function ($ap) use ($hospitalId, $now) {
        if (($ap['hospital_id'] ?? '') !== $hospitalId) return false;
        $status = $ap['status'] ?? '';
        if (!in_array($status, ['confirmed', 'checkin'], true)) return false;
        $ts = strtotime($ap['date'] ?? '');
        return $ts !== false && $ts >= $now;
    });
    usort($up, fn($a, $b) => strtotime($a['date'] ?? '0') <=> strtotime($b['date'] ?? '0'));
    return array_values($up);
}

function list_active(array $data, string $hospitalId): array
{
    $act = array_filter($data['appointments'], fn($ap) => ($ap['hospital_id'] ?? '') === $hospitalId && ($ap['status'] ?? '') === 'in_progress');
    usort($act, fn($a, $b) => strcmp($a['date'] ?? '', $b['date'] ?? ''));
    return array_values($act);
}

function count_today_done(array $data, string $hospitalId): int
{
    $n = 0;
    foreach ($data['appointments'] as $ap) {
        if (($ap['hospital_id'] ?? '') !== $hospitalId) continue;
        if (($ap['status'] ?? '') !== 'done') continue;
        if (is_today_ts($ap['date'] ?? null)) $n++;
    }
    return $n;
}

function count_today_all(array $data, string $hospitalId): int
{
    $n = 0;
    foreach ($data['appointments'] as $ap) {
        if (($ap['hospital_id'] ?? '') !== $hospitalId) continue;
        if (is_today_ts($ap['date'] ?? null)) $n++;
    }
    return $n;
}

function count_requested(array $data, string $hospitalId): int
{
    return count(list_requested($data, $hospitalId));
}

function first_active_appointment_for_doctor(array $data, string $doctorId): ?array
{
    $items = array_filter($data['appointments'], fn($ap) => ($ap['doctor_id'] ?? '') === $doctorId && ($ap['status'] ?? '') === 'in_progress');
    usort($items, fn($a, $b) => strtotime($a['date'] ?? '0') <=> strtotime($b['date'] ?? '0'));
    foreach ($items as $ap) return $ap;
    return null;
}

function doctor_today_stats(array $data, string $doctorId): array
{
    $todayAppointments = array_filter($data['appointments'], function ($ap) use ($doctorId) {
        if (($ap['doctor_id'] ?? '') !== $doctorId) return false;
        $date = $ap['date'] ?? null;
        return is_today_ts($date);
    });
    $done = 0;
    $remaining = 0;
    foreach ($todayAppointments as $ap) {
        $status = $ap['status'] ?? 'requested';
        if ($status === 'done') $done++;
        else $remaining++;
    }
    return [
        'total' => count($todayAppointments),
        'done' => $done,
        'remaining' => $remaining,
    ];
}

function upcoming_appointments_for_doctor(array $data, string $doctorId): array
{
    $now = time();
    $upcoming = array_filter($data['appointments'], function ($ap) use ($doctorId, $now) {
        if (($ap['doctor_id'] ?? '') !== $doctorId) return false;
        $dateStr = $ap['date'] ?? null;
        $ts = $dateStr ? strtotime($dateStr) : null;
        if (!$ts) return false;
        if ($ts < $now) return false;
        $status = $ap['status'] ?? 'requested';
        return in_array($status, ['requested', 'confirmed'], true);
    });
    usort($upcoming, function ($a, $b) {
        return strtotime($a['date'] ?? '0') <=> strtotime($b['date'] ?? '0');
    });
    return array_values($upcoming);
}

function get_patient_by_id(array $data, string $patientId): ?array
{
    foreach ($data['patients'] as $p) {
        if (($p['id'] ?? '') === $patientId) return $p;
    }
    return null;
}

function add_prescription(array &$data, string $doctorId, string $patientId, string $text): array
{
    $id = next_id('rx');
    $rx = [
        'id' => $id,
        'doctor_id' => $doctorId,
        'patient_id' => $patientId,
        'text' => $text,
        'created_at' => date('Y-m-d H:i:s'),
    ];
    $data['prescriptions'][] = $rx;
    return $rx;
}

function add_note(array &$data, string $doctorId, string $patientId, string $text): array
{
    $id = next_id('note');
    $note = [
        'id' => $id,
        'doctor_id' => $doctorId,
        'patient_id' => $patientId,
        'text' => $text,
        'created_at' => date('Y-m-d H:i:s'),
    ];
    $data['notes'][] = $note;
    return $note;
}

function add_test_order(array &$data, string $doctorId, string $patientId, string $testName, string $remarks): array
{
    $id = next_id('test');
    $order = [
        'id' => $id,
        'doctor_id' => $doctorId,
        'patient_id' => $patientId,
        'name' => $testName,
        'remarks' => $remarks,
        'created_at' => date('Y-m-d H:i:s'),
    ];
    $data['tests'][] = $order;
    return $order;
}

function add_test_report(array &$data, string $doctorId, string $patientId, string $testName, string $reportText): array
{
    $id = next_id('report');
    $report = [
        'id' => $id,
        'doctor_id' => $doctorId,
        'patient_id' => $patientId,
        'name' => $testName,
        'report' => $reportText,
        'created_at' => date('Y-m-d H:i:s'),
    ];
    $data['test_reports'][] = $report;
    return $report;
}
