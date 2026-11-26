<?php
require 'config.php';
start_session();
$_SESSION = [];
session_destroy();
out(['success' => true]);
