<?php
error_reporting(E_ALL); ini_set('display_errors', 1);
require_once 'lib/helpers.php';
require_once 'config.php';
require_once 'src/Database.php';
require_once 'src/Repository/AbstractRepository.php';
require_once 'src/Repository/ClientRepository.php';
require_once 'src/Repository/CarRepository.php';
require_once 'src/Repository/ServiceRepository.php';
require_once 'src/Repository/AppointmentRepository.php';

$pageTitle = 'Онлайн-запись'; // <-- меняйте под каждую страницу
require_once 'includes/header.php';

$db = Database::getInstance(require 'config.php');
$pdo = $db->getConnection();
$clientRepo = new ClientRepository($pdo);
$carRepo = new CarRepository($pdo);
$serviceRepo = new ServiceRepository($pdo);
$aptRepo = new AppointmentRepository($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book'])) {
    if (!verify_csrf()) die('CSRF Error');
    
    $data = [
        'client_id' => (int)$_POST['client_id'],
        'car_id' => (int)$_POST['car_id'],
        'service_id' => (int)$_POST['service_id'],
        'specialist_id' => (int)$_POST['specialist_id'],
        'appointment_datetime' => $_POST['datetime']
    ];

    // Валидация
    if (!$data['client_id'] || !$data['car_id'] || !$data['service_id'] || !$data['specialist_id'] || !$data['appointment_datetime']) {
        $error = 'Заполните все обязательные поля';
    } elseif (strtotime($data['appointment_datetime']) < strtotime('today')) {
        $error = 'Дата не может быть в прошлом';
    } else {
        try {
            $id = $aptRepo->createAppointment($data);
            $code = strtoupper(substr(md5($id . time()), 0, 6));
            $success = "✅ Запись создана! Код бронирования: <b>$code</b>";
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$clients = $clientRepo->findAll();
$services = $serviceRepo->findAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Онлайн-запись</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .slot-btn.active { background-color: #0d6efd; color: white; border-color: #0d6efd; }
        .slot-btn:disabled { opacity: 0.5; cursor: not-allowed; }
    </style>
</head>
<body class="bg-light">
<div class="container py-5">
    <h1 class="mb-4">📅 Создание записи</h1>
    
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
        <a href="booking.php" class="btn btn-primary">Новая запись</a>
    <?php elseif (!empty($error)): ?>
        <div class="alert alert-danger"><?= escape($error) ?></div>
    <?php endif; ?>

    <?php if (empty($success)): ?>
    <form method="POST" class="bg-white p-4 rounded shadow" id="bookingForm">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label>Клиент *</label>
            <select name="client_id" id="clientSelect" class="form-select" required onchange="loadCars()">
                <option value="">— Выберите клиента —</option>
                <?php foreach ($clients as $c): ?>
                    <option value="<?= $c['client_id'] ?>"><?= escape($c['last_name'] . ' ' . $c['first_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label>Автомобиль *</label>
            <select name="car_id" id="carSelect" class="form-select" required disabled>
                <option>— Сначала выберите клиента —</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Услуга *</label>
            <select name="service_id" id="serviceSelect" class="form-select" required onchange="loadSpecialists()">
                <option value="">— Выберите услугу —</option>
                <?php foreach ($services as $s): ?>
                    <option value="<?= $s['service_id'] ?>" data-duration="<?= $s['duration_minutes'] ?? 60 ?>">
                        <?= escape($s['service_name']) ?> (<?= number_format($s['price'], 0) ?> ₽, ~<?= $s['duration_minutes'] ?? 60 ?> мин)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label>Специалист *</label>
            <select name="specialist_id" id="specialistSelect" class="form-select" required disabled onchange="loadSlots()">
                <option>— Сначала выберите услугу —</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Дата *</label>
            <input type="date" name="date" id="dateInput" class="form-control" min="<?= date('Y-m-d') ?>" required onchange="loadSlots()" disabled>
        </div>

        <div class="mb-4">
            <label>Доступное время *</label>
            <div id="slotsContainer" class="d-flex flex-wrap gap-2 mt-2">
                <span class="text-muted small">Выберите специалиста и дату</span>
            </div>
            <input type="hidden" name="datetime" id="selectedDatetime" required>
        </div>

        <button type="submit" name="book" class="btn btn-success btn-lg w-100">Подтвердить запись</button>
    </form>
    <?php endif; ?>
</div>

<script>
    function loadCars() {
        const cid = document.getElementById('clientSelect').value;
        const sel = document.getElementById('carSelect');
        sel.innerHTML = '<option>Загрузка...</option>'; sel.disabled = true;
        if (!cid) { sel.innerHTML = '<option>— Сначала выберите клиента —</option>'; return; }
        
        fetch(`api/cars.php?client_id=${cid}`)
            .then(r => r.json())
            .then(data => {
                sel.innerHTML = '<option value="">— Выберите автомобиль —</option>';
                data.forEach(c => {
                    let o = document.createElement('option');
                    o.value = c.car_id; o.text = `${c.make} ${c.model} (${c.year})`;
                    sel.add(o);
                });
                sel.disabled = false;
            });
    }

    function loadSpecialists() {
        const sel = document.getElementById('specialistSelect');
        const dateInp = document.getElementById('dateInput');
        sel.innerHTML = '<option>Загрузка...</option>'; sel.disabled = true;
        dateInp.disabled = true;
        document.getElementById('slotsContainer').innerHTML = '';
        document.getElementById('selectedDatetime').value = '';

        fetch('api/specialists.php')
            .then(r => r.json())
            .then(data => {
                sel.innerHTML = '<option value="">— Выберите специалиста —</option>';
                data.forEach(s => {
                    let o = document.createElement('option');
                    o.value = s.specialist_id; o.text = `${s.name} (${s.specialty})`;
                    sel.add(o);
                });
                sel.disabled = false;
                dateInp.disabled = false;
            });
    }

    function loadSlots() {
        const sid = document.getElementById('specialistSelect').value;
        const date = document.getElementById('dateInput').value;
        const svcSel = document.getElementById('serviceSelect');
        const duration = svcSel.options[svcSel.selectedIndex]?.dataset.duration || 60;
        const container = document.getElementById('slotsContainer');
        const hidden = document.getElementById('selectedDatetime');
        
        if (!sid || !date) return;
        
        container.innerHTML = '<div class="spinner-border text-primary"></div> Поиск слотов...';
        hidden.value = '';

        fetch(`api/slots.php?specialist_id=${sid}&date=${date}&duration=${duration}`)
            .then(r => r.json())
            .then(data => {
                container.innerHTML = '';
                if (data.error) { container.innerHTML = `<span class="text-danger">${data.error}</span>`; return; }
                if (data.slots.length === 0) { container.innerHTML = '<span class="text-danger">Нет свободного времени на выбранную дату</span>'; return; }
                
                data.slots.forEach(time => {
                    let btn = document.createElement('button');
                    btn.type = 'button'; btn.className = 'btn btn-outline-primary slot-btn'; btn.innerText = time;
                    btn.onclick = function() {
                        document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('active'));
                        this.classList.add('active');
                        hidden.value = `${date} ${time}:00`;
                    };
                    container.appendChild(btn);
                });
            })
            .catch(() => container.innerHTML = '<span class="text-danger">Ошибка загрузки</span>');
    }
</script>
</body>
</html>

<?php require_once 'includes/footer.php'; ?>