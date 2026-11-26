<?php
// Open http://localhost/valeria-dental/api/test_connection.php to check XAMPP and the database are talking.
require 'config.php';
$db = db();
$n = $db->query('SELECT COUNT(*) FROM patients')->fetch_row()[0];
out(['success' => true, 'message' => 'Connected to ' . DB_NAME, 'patients_in_database' => (int)$n, 'php' => PHP_VERSION]);
