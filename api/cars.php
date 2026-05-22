<?php
// api/cars.php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // 🔥 ВАЖНЫЙ ПОРЯДОК: сначала Database, потом AbstractRepository, потом конкретные репозитории
    require_once '../src/Database.php';
    require_once '../src/Repository/AbstractRepository.php';  // ← Родительский класс ПЕРЕД дочерним!
    require_once '../src/Repository/CarRepository.php';
    
    $clientId = isset($_GET['client_id']) ? (int)$_GET['client_id'] : 0;
    
    if ($clientId <= 0) {
        echo json_encode(['error' => 'Неверный ID клиента']);
        exit;
    }
    
    $config = require '../config.php';
    $db = Database::getInstance($config);
    $repo = new CarRepository($db->getConnection());
    
    $cars = $repo->getCarsByClientId($clientId);
    
    echo json_encode($cars);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}