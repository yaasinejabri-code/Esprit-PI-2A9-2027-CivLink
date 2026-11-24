<?php
require_once __DIR__.'/../../../Models/Auth.php'; authStart();
header('Content-Type: application/json; charset=utf-8'); header('Cache-Control: no-store');
echo json_encode(['connected'=>isset($_SESSION['user'])]);
