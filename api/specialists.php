<?php
header('Content-Type: application/json');
error_reporting(E_ALL); ini_set('display_errors', 1);

try {
    require_once '../src/Database.php';
    require_once '../src/Repository/AbstractRepository.php';
    require_once '../src/Repository/SpecialistRepository.php';

    $config = require '../config.php';
    $db = Database::getInstance($config);
    $repo = new SpecialistRepository($db->getConnection());

    echo json_encode($repo->findAllActive());
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}