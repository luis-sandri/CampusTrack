<?php
$id     = $_GET['id']     ?? null;
$action = $_GET['action'] ?? null;

if ($id) {
    $_SERVER['PATH_INFO'] = '/eventos/' . $id;
} elseif ($action === 'proximos') {
    $_SERVER['PATH_INFO'] = '/eventos/proximos';
} else {
    $_SERVER['PATH_INFO'] = '/eventos';
}
require __DIR__ . '/index.php';
