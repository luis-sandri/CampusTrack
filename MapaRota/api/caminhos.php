<?php
$action = $_GET['action'] ?? null;
$_SERVER['PATH_INFO'] = ($action === 'grafo') ? '/caminhos/grafo' : '/caminhos';
require __DIR__ . '/index.php';
