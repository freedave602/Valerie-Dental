<?php
// Staff-only reports. Every report is a prepared SELECT; nothing from the URL is concatenated into SQL.
require 'config.php';
require_staff();
$db = db();
$type = $_GET['type'] ?? 'future';

$apt = "SELECT a.appointment_id, a.appointment_date, TIME_FORMAT(a.appointment_time,'%H:%i') AS appointment_time,
               CONCAT(p.first_name,' ',p.last_name) AS patient, p.phone, t.name AS treatment, a.status, a.notes
        FROM appointments a JOIN patients p ON p.patient_id = a.patient_id JOIN treatments t ON t.treatment_id = a.treatment_id";
$order = ' ORDER BY a.appointment_date, a.appointment_time';

switch ($type) {
    case 'summary':
        $q = fn($sql) => (int)$db->query($sql)->fetch_row()[0];
        out(['success' => true,
            'patients' => $q('SELECT COUNT(*) FROM patients'),
            'today'    => $q("SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE() AND status <> 'Cancelled'"),
            'upcoming' => $q('SELECT COUNT(*) FROM v_future_appointments'),
            'unread'   => $q('SELECT COUNT(*) FROM messages WHERE is_read = 0'),
            'byTreatment' => $db->query("SELECT t.name, COUNT(*) AS total FROM appointments a JOIN treatments t ON t.treatment_id=a.treatment_id WHERE a.status<>'Cancelled' GROUP BY t.treatment_id ORDER BY total DESC")->fetch_all(MYSQLI_ASSOC)]);
    case 'patients':
        out(['success' => true, 'rows' => $db->query('SELECT * FROM v_patient_list ORDER BY last_name, first_name')->fetch_all(MYSQLI_ASSOC)]);
    case 'future':
        out(['success' => true, 'rows' => $db->query('SELECT * FROM v_future_appointments')->fetch_all(MYSQLI_ASSOC)]);
    case 'past':
        out(['success' => true, 'rows' => $db->query('SELECT * FROM v_past_appointments')->fetch_all(MYSQLI_ASSOC)]);
    case 'all':
        out(['success' => true, 'rows' => $db->query($apt . $order)->fetch_all(MYSQLI_ASSOC)]);
    case 'today':
        out(['success' => true, 'rows' => $db->query($apt . ' WHERE a.appointment_date = CURDATE()' . $order)->fetch_all(MYSQLI_ASSOC)]);
    case 'week':
        out(['success' => true, 'rows' => $db->query($apt . ' WHERE YEARWEEK(a.appointment_date,1) = YEARWEEK(CURDATE(),1)' . $order)->fetch_all(MYSQLI_ASSOC)]);
    case 'month':
        out(['success' => true, 'rows' => $db->query($apt . ' WHERE YEAR(a.appointment_date)=YEAR(CURDATE()) AND MONTH(a.appointment_date)=MONTH(CURDATE())' . $order)->fetch_all(MYSQLI_ASSOC)]);
    case 'patient':
        $pid = (int)($_GET['patient_id'] ?? 0);
        $s = $db->prepare($apt . ' WHERE a.patient_id = ?' . $order);
        $s->bind_param('i', $pid); $s->execute();
        out(['success' => true, 'rows' => $s->get_result()->fetch_all(MYSQLI_ASSOC)]);
    case 'messages':
        out(['success' => true, 'rows' => $db->query('SELECT message_id, name, email, message, is_read, sent_at FROM messages ORDER BY sent_at DESC')->fetch_all(MYSQLI_ASSOC)]);
    default:
        out(['success' => false, 'error' => 'Unknown report.'], 400);
}
