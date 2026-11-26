<?php
require 'config.php';
only_post();
$d = body();
$name = field($d, 'name', 100); $email = field($d, 'email', 120); $msg = field($d, 'message', 2000);
if (!$name || !$msg || !filter_var($email, FILTER_VALIDATE_EMAIL)) out(['success' => false, 'error' => 'Please complete all fields with a valid email.'], 422);

$s = db()->prepare('INSERT INTO messages (name, email, message) VALUES (?,?,?)');
$s->bind_param('sss', $name, $email, $msg);
$s->execute();
out(['success' => true]);
