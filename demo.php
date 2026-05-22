<?php
declare(strict_types=1);
// 🔥 Включаем отображение ВСЕХ ошибок (только для отладки!)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// ... остальной код

// Безопасная проверка конфигурации
if (!file_exists('config.php')) {
    die('❌ Ошибка: скопируйте config.example.php в config.php и укажите параметры подключения.');
}

require_once 'config.php';
require_once 'src/Database.php';
require_once 'src/Exception/RepositoryException.php';
require_once 'src/Repository/AbstractRepository.php';
require_once 'src/Repository/ClientRepository.php';
require_once 'src/Repository/CarRepository.php';
require_once 'src/Repository/ServiceRepository.php';
require_once 'src/Repository/AppointmentRepository.php';

echo "<pre>";
echo "🚀 Запуск демонстрации уровня доступа к данным (DAL)\n";
echo str_repeat("=", 50) . "\n\n";

try {
    // 1. Инициализация БД
    $db = Database::getInstance(require 'config.php');
    $pdo = $db->getConnection();
    echo "✅ Подключение к БД установлено.\n\n";

    // 2. Создание репозиториев
    $clientRepo = new ClientRepository($pdo);
    $carRepo = new CarRepository($pdo);
    $serviceRepo = new ServiceRepository($pdo);
    $appointmentRepo = new AppointmentRepository($pdo);

    // 3. Тест findAll с фильтрацией и сортировкой
    echo "👥 Клиенты (поиск по фамилии 'Иванов'):\n";
    $clients = $clientRepo->findAll(
        where: ['last_name' => 'Иванов'],
        orderBy: ['created_at' => 'DESC'],
        limit: 5
    );
    print_r($clients);

    // 4. Тест findById
    echo "\n🔍 Клиент ID=1:\n";
    print_r($clientRepo->findById(1));

    // 5. Специфичные методы CarRepository
    echo "\n🚗 Автомобили клиента ID=1:\n";
    $cars = $carRepo->getCarsByClientId(1);
    print_r($cars);

    echo "\n🔎 Поиск по VIN:\n";
    $car = $carRepo->findByVin('JTDBR32E050123456');
    print_r($car);

    // 6. Транзакционное создание записи (использует триггер БД для проверки запчастей)
    echo "\n📝 Создание записи на обслуживание...\n";
    $appId = $appointmentRepo->createAppointment(
        clientId: 1, carId: 1, serviceId: 5,
        datetime: '2026-07-15 10:00:00', status: 'запланировано'
    );
    echo "✅ Запись создана. ID: $appId\n";

    // 7. Обновление статуса
    echo "\n🔄 Обновление статуса записи $appId → 'в работе'\n";
    $appointmentRepo->updateStatus($appId, 'в работе');

    // 8. Удаление записи
    echo "\n🗑️ Удаление записи $appId\n";
    $appointmentRepo->delete($appId);
    echo "✅ Запись удалена.\n";

    // 9. Демонстрация обработки ошибок (дубликат email)
    echo "\n⚠️ Тест обработки дубликата UNIQUE constraint:\n";
    try {
        $clientRepo->insert([
            'last_name' => 'Тест', 'first_name' => 'Ошибка', 'patronymic' => '',
            'phone' => '+79998887766', 'email' => 'ivanov@example.com', // дубликат
            'birth_date' => '1990-01-01'
        ]);
    } catch (RepositoryException $e) {
        echo "❌ Ожидаемая ошибка: " . $e->getMessage() . "\n";
    }

} catch (\Throwable $e) {
    echo "\n💥 Критическая ошибка: " . $e->getMessage() . "\n";
    if ($e->getPrevious()) {
        echo "🔍 Причина: " . $e->getPrevious()->getMessage() . "\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "✅ Демонстрация завершена.\n";
echo "</pre>";