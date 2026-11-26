<?php
require 'config.php';
only_post();
start_session();
$d = body();
$email = strtolower(field($d, 'email', 120)); $password = (string)($d['password'] ?? '');

$s = db()->prepare('SELECT staff_id, full_name, role, password_hash FROM staff WHERE email = ?');
$s->bind_param('s', $email); $s->execute();
$row = $s->get_result()->fetch_assoc();

if (!$row || !password_verify($password, $row['password_hash'])) {
    usleep(400000); // slows down password guessing
    out(['success' => false, 'error' => 'Invalid email or password.'], 401);
}
session_regenerate_id(true);
$_SESSION['staff_id'] = $row['staff_id'];
$_SESSION['staff_name'] = $row['full_name'];
out(['success' => true, 'name' => $row['full_name'], 'role' => $row['role']]);
