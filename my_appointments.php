<?php
// Patient portal lookup: email + date of birth must both match.
require 'config.php';
only_post();
$d = body();
$email = strtolower(field($d, 'email', 120)); $dob = field($d, 'dob', 10);
$db = db();
$s = $db->prepare('SELECT patient_id, first_name FROM patients WHERE email = ? AND dob = ?');
$s->bind_param('ss', $email, $dob); $s->execute();
$p = $s->get_result()->fetch_assoc();
if (!$p) out(['success' => false, 'error' => 'No record matches those details.'], 404);

$s = $db->prepare("SELECT a.appointment_id, a.appointment_date, TIME_FORMAT(a.appointment_time,'%H:%i') AS appointment_time, t.name AS treatment, a.status
                   FROM appointments a JOIN treatments t ON t.treatment_id = a.treatment_id
                   WHERE a.patient_id = ? ORDER BY a.appointment_date DESC, a.appointment_time DESC");
$s->bind_param('i', $p['patient_id']); $s->execute();
out(['success' => true, 'firstName' => $p['first_name'], 'rows' => $s->get_result()->fetch_all(MYSQLI_ASSOC)]);
