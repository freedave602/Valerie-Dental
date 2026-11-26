<?php
require 'config.php';
only_post();
$d = body();

$first = field($d, 'firstName', 60);  $last = field($d, 'lastName', 60);
$email = strtolower(field($d, 'email', 120)); $phone = field($d, 'phone', 25);
$dob = field($d, 'dob', 10); $address = field($d, 'address'); $history = field($d, 'medicalHistory', 2000);
$consent = !empty($d['consent']) ? 1 : 0;

if (!$first || !$last || !$address) out(['success' => false, 'error' => 'Please fill in every required field.'], 422);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) out(['success' => false, 'error' => 'That email address does not look right.'], 422);
if (!preg_match('/^\+?[0-9 ]{9,15}$/', $phone)) out(['success' => false, 'error' => 'Enter a valid phone number, e.g. 024 400 0111.'], 422);
$dt = DateTime::createFromFormat('Y-m-d', $dob);
if (!$dt || $dt->format('Y-m-d') !== $dob || $dob > date('Y-m-d')) out(['success' => false, 'error' => 'Enter a valid date of birth.'], 422);
if (!$consent) out(['success' => false, 'error' => 'Please tick the consent box so we can store your details.'], 422);

try {
    $stmt = db()->prepare('INSERT INTO patients (first_name,last_name,email,phone,dob,address,medical_history,consent_given) VALUES (?,?,?,?,?,?,?,1)');
    $stmt->bind_param('sssssss', $first, $last, $email, $phone, $dob, $address, $history);
    $stmt->execute();
    out(['success' => true, 'patientId' => $stmt->insert_id]);
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() === 1062) out(['success' => false, 'error' => 'This email is already registered. You can go straight to booking.'], 409);
    out(['success' => false, 'error' => 'Registration failed. Please try again.'], 500);
}
