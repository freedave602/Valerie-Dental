<?php
require 'config.php';
only_post();
require_staff();
$d = body();
$id = (int)($d['appointmentId'] ?? 0); $status = field($d, 'status', 12);
if (!in_array($status, ['Scheduled', 'Completed', 'Cancelled'], true)) out(['success' => false, 'error' => 'Invalid status.'], 422);
try {
    $s = db()->prepare('UPDATE appointments SET status = ? WHERE appointment_id = ?');
    $s->bind_param('si', $status, $id); $s->execute();
    out(['success' => true]);
} catch (mysqli_sql_exception $e) {
    out(['success' => false, 'error' => 'That slot has since been rebooked, so this appointment cannot be restored.'], 409);
}
