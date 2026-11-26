<?php
require 'config.php';
only_post();
$d = body();

$email = strtolower(field($d, 'email', 120)); $date = field($d, 'appointmentDate', 10);
$time = field($d, 'appointmentTime', 5);      $code = field($d, 'treatmentType', 20);
$notes = field($d, 'notes', 500);

$dt = DateTime::createFromFormat('Y-m-d', $date);
if (!$dt || $dt->format('Y-m-d') !== $date) out(['success' => false, 'error' => 'Choose a valid date.'], 422);
if ($date < date('Y-m-d')) out(['success' => false, 'error' => 'That date has already passed.'], 422);
if (!in_array($time, slots_for($date), true)) out(['success' => false, 'error' => 'We are closed at that time. Please pick one of the listed slots.'], 422);

$db = db();
// Lookup 1: find the patient from the email they registered with
$s = $db->prepare('SELECT patient_id, first_name FROM patients WHERE email = ?');
$s->bind_param('s', $email); $s->execute();
$patient = $s->get_result()->fetch_assoc();
if (!$patient) out(['success' => false, 'error' => 'We could not find that email. Please register first.', 'needsRegistration' => true], 404);

// Lookup 2: treatment code -> id
$s = $db->prepare('SELECT treatment_id, name FROM treatments WHERE code = ?');
$s->bind_param('s', $code); $s->execute();
$treatment = $s->get_result()->fetch_assoc();
if (!$treatment) out(['success' => false, 'error' => 'Choose a treatment from the list.'], 422);

try {
    $s = $db->prepare("INSERT INTO appointments (patient_id, treatment_id, appointment_date, appointment_time, notes, status) VALUES (?,?,?,?,?, 'Scheduled')");
    $s->bind_param('iisss', $patient['patient_id'], $treatment['treatment_id'], $date, $time, $notes);
    $s->execute();
    out(['success' => true, 'appointmentId' => $s->insert_id, 'firstName' => $patient['first_name'], 'treatment' => $treatment['name']]);
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() === 1062) out(['success' => false, 'error' => 'That slot was just taken. Please choose another time.'], 409);
    out(['success' => false, 'error' => 'Booking failed. Please try again.'], 500);
}
