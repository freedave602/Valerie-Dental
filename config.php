<?php
// Shared setup for every endpoint: database connection, JSON helpers, staff guard.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
date_default_timezone_set('Africa/Accra');

const DB_HOST = 'localhost';
const DB_USER = 'root';
const DB_PASS = '';              // XAMPP default is an empty root password
const DB_NAME = 'valeria_dental';

// Opening hours: Mon-Fri full day, Sat mornings, Sun closed
const SLOTS_WEEKDAY  = ['09:00','09:30','10:00','10:30','11:00','11:30','14:00','14:30','15:00','15:30','16:00','16:30'];
const SLOTS_SATURDAY = ['09:00','09:30','10:00','10:30','11:00','11:30'];

function out($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data);
    exit;
}

function db() {
    static $conn = null;
    if ($conn) return $conn;
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $conn->set_charset('utf8mb4');
    } catch (mysqli_sql_exception $e) {
        out(['success' => false, 'error' => 'Cannot reach the database. Check MySQL is running in XAMPP and valeria_dental.sql has been imported.'], 500);
    }
    return $conn;
}

function body() {
    $d = json_decode(file_get_contents('php://input'), true);
    return is_array($d) ? $d : [];
}

function only_post() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(['success' => false, 'error' => 'POST required.'], 405);
}

function field($arr, $key, $max = 255) {
    $v = trim((string)($arr[$key] ?? ''));
    return function_exists('mb_substr') ? mb_substr($v, 0, $max) : substr($v, 0, $max);
}

function start_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params(['httponly' => true, 'samesite' => 'Strict', 'path' => '/']);
        session_start();
    }
}

function require_staff() {
    start_session();
    if (empty($_SESSION['staff_id'])) out(['success' => false, 'error' => 'Staff login required.'], 401);
}

function slots_for($date) {
    $dow = (int)date('N', strtotime($date));
    if ($dow === 7) return [];
    return $dow === 6 ? SLOTS_SATURDAY : SLOTS_WEEKDAY;
}
