<?php
header('Content-Type: application/json');
error_reporting(E_ALL); ini_set('display_errors', 1);

try {
    require_once '../src/Database.php';
    require_once '../src/Repository/AbstractRepository.php';
    require_once '../src/Repository/AppointmentRepository.php';

    $config = require '../config.php';
    $db = Database::getInstance($config);
    $repo = new AppointmentRepository($db->getConnection());

    $specialistId = (int)($_GET['specialist_id'] ?? 0);
    $date = $_GET['date'] ?? '';
    $duration = (int)($_GET['duration'] ?? 60);

    if (!$specialistId || !$date) {
        echo json_encode(['error' => 'Не указаны специалист или дата']);
        exit;
    }

    $slots = $repo->getAvailableSlots($specialistId, $date, $duration);
    echo json_encode(['slots' => $slots]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}