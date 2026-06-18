<?php
require_once __DIR__ . '/includes/init.php';
Auth::logout();
header('Location: ' . BASE_URL . '/index.php');
exit;
