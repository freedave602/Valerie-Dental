<?php
// Lookup: which times are still free on a given date?
require 'config.php';
$date = $_GET['date'] ?? '';
$dt = DateTime::createFromFormat('Y-m-d', $date);
if (!$dt || $dt->format('Y-m-d') !== $date) out(['success' => false, 'error' => 'Invalid date.'], 422);

$stmt = db()->prepare("SELECT TIME_FORMAT(appointment_time,'%H:%i') FROM appointments WHERE appointment_date = ? AND status <> 'Cancelled'");
$stmt->bind_param('s', $date);
$stmt->execute();
$taken = array_column($stmt->get_result()->fetch_all(), 0);
$all = slots_for($date);
out(['success' => true, 'closed' => !$all, 'slots' => array_map(fn($t) => ['time' => $t, 'free' => !in_array($t, $taken)], $all)]);
