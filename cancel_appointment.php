<?php
require 'config.php';
only_post();
$d = body();
$email = strtolower(field($d, 'email', 120)); $dob = field($d, 'dob', 10); $id = (int)($d['appointmentId'] ?? 0);
$s = db()->prepare("UPDATE appointments a JOIN patients p ON p.patient_id = a.patient_id
                    SET a.status = 'Cancelled'
                    WHERE a.appointment_id = ? AND p.email = ? AND p.dob = ? AND a.status = 'Scheduled' AND a.appointment_date >= CURDATE()");
$s->bind_param('iss', $id, $email, $dob); $s->execute();
out($s->affected_rows ? ['success' => true] : ['success' => false, 'error' => 'That appointment cannot be cancelled.'], $s->affected_rows ? 200 : 422);
