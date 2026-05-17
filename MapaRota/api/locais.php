<?php
$id     = $_GET['id']     ?? null;
$action = $_GET['action'] ?? null;

if ($id) {
    $_SERVER['PATH_INFO'] = '/locais/' . $id;
} elseif ($action === 'search') {
    $_SERVER['PATH_INFO'] = '/locais/search';
} else {
    $_SERVER['PATH_INFO'] = '/locais';
}
require __DIR__ . '/index.php';
