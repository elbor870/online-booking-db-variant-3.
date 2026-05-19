<?php
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'src/Database.php';
require_once 'src/Exception/RepositoryException.php';
require_once 'src/Repository/AbstractRepository.php';
require_once 'src/Repository/ClientRepository.php';
require_once 'src/Repository/ServiceRepository.php';
require_once 'src/Repository/AppointmentRepository.php';

echo "<pre>";

try {
    // 1. Подключение
    $config = require 'config.php';
    $db = Database::getInstance($config);
    $pdo = $db->getConnection();
    echo "✅ Подключение к БД установлено.\n\n";

    // 2. Инициализация репозиториев
    $clientRepo = new ClientRepository($pdo);
    $serviceRepo = new ServiceRepository($pdo);
    $appointmentRepo = new AppointmentRepository($pdo);

    // 3. findAll с фильтрацией и сортировкой
    $clients = $clientRepo->findAll(
        where: ['last_name' => 'Иванов'],
        orderBy: ['created_at' => 'DESC'],
        limit: 5
    );
    echo "👥 Клиенты (фильтр last_name='Иванов'):\n";
    print_r($clients);

    // 4. findById
    $client = $clientRepo->findById(1);
    echo "\n🔍 Клиент по ID=1:\n";
    print_r($client);

    // 5. Специфические методы
    $services = $serviceRepo->getByCategory(3); // слесарные
    echo "\n🔧 Слесарные услуги:\n";
    print_r($services);

    // 6. Создание записи (транзакция + триггер проверки запчастей)
    echo "\n📝 Создание новой записи...\n";
    $newId = $appointmentRepo->createAppointment(
        clientId: 1, carId: 1, serviceId: 5, datetime: '2026-06-10 14:30:00', status: 'запланировано'
    );
    echo "✅ Запись создана. ID: $newId\n";

    // 7. Обновление статуса
    $appointmentRepo->updateStatus($newId, 'в работе');
    echo "✅ Статус записи $newId изменён на 'в работе'.\n";

    // 8. Удаление
    $appointmentRepo->delete($newId);
    echo "✅ Запись $newId удалена.\n";

} catch (RepositoryException $e) {
    echo "❌ Ошибка репозитория: " . $e->getMessage() . "\n";
    if ($e->getPrevious()) {
        echo "🔍 Причина: " . $e->getPrevious()->getMessage() . "\n";
    }
} catch (\Exception $e) {
    echo "💥 Критическая ошибка: " . $e->getMessage() . "\n";
}

echo "</pre>";
