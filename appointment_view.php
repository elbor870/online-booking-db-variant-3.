<?php
error_reporting(E_ALL); ini_set('display_errors', 1);

// 1️⃣ Подключение зависимостей
require_once 'lib/helpers.php';
require_once 'config.php';
require_once 'src/Database.php';
require_once 'src/Repository/AbstractRepository.php';
require_once 'src/Repository/AppointmentRepository.php';
require_once 'src/Repository/SpecialistRepository.php';

$db = Database::getInstance(require 'config.php');
$pdo = $db->getConnection();
$aptRepo = new AppointmentRepository($pdo);
$specRepo = new SpecialistRepository($pdo);

$id = (int)($_GET['id'] ?? 0);

// 2️⃣ ОБРАБОТКА POST-ЗАПРОСА (СТРОГО ДО ЛЮБОГО HTML!)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reschedule'])) {
    if (!verify_csrf()) die('CSRF Error');
    try {
        $aptRepo->reschedule($id, (int)$_POST['specialist_id'], $_POST['new_datetime']);
        flash('success', '✅ Запись успешно перенесена. Старое время освобождено.');
        header("Location: appointment_view.php?id=$id");
        exit; // Прерываем выполнение, чтобы не рендерить форму повторно
    } catch (Exception $e) {
        $error = $e->getMessage();
        // При ошибке НЕ делаем редирект, чтобы показать сообщение пользователю
    }
}

// 3️⃣ Подготовка данных для отображения
$apt = $aptRepo->getAppointmentWithDetails($id);
if (!$apt) die('Запись не найдена');

$specialists = $specRepo->findAllActive();

// 4️⃣ ПОДКЛЮЧЕНИЕ ШАПКИ (ВЫВОД HTML НАЧИНАЕТСЯ ЗДЕСЬ)
$pageTitle = 'Детали записи #' . $id;
require_once 'includes/header.php';
?>

<div class="container py-5">
    <h1>📋 Запись #<?= $id ?></h1>
    <?= flash() ?>
    <?php if (!empty($error)): ?><div class="alert alert-danger"><?= escape($error) ?></div><?php endif; ?>

    <div class="bg-white p-4 rounded shadow-sm mb-4">
        <dl class="row">
            <dt class="col-sm-3">Дата и время</dt>
            <dd class="col-sm-9"><?= date('d.m.Y H:i', strtotime($apt['appointment_datetime'])) ?></dd>
            
            <dt class="col-sm-3">Клиент</dt>
            <dd class="col-sm-9"><?= escape($apt['last_name'] . ' ' . $apt['first_name']) ?></dd>
            
            <dt class="col-sm-3">Телефон</dt>
            <dd class="col-sm-9"><?= escape($apt['phone']) ?></dd>
            
            <dt class="col-sm-3">Автомобиль</dt>
            <dd class="col-sm-9"><?= escape($apt['make'] . ' ' . $apt['model']) ?> (<?= $apt['year'] ?>)</dd>
            
            <?php if (!empty($apt['vin'])): ?>
            <dt class="col-sm-3">VIN</dt>
            <dd class="col-sm-9"><code><?= escape($apt['vin']) ?></code></dd>
            <?php endif; ?>
            
            <dt class="col-sm-3">Услуга</dt>
            <dd class="col-sm-9"><?= escape($apt['service_name']) ?> (<?= number_format($apt['price'], 0) ?> ₽)</dd>
            
            <dt class="col-sm-3">Специалист</dt>
            <dd class="col-sm-9"><?= escape($apt['specialist_name'] ?? 'Не назначен') ?></dd>
            
            <dt class="col-sm-3">Статус</dt>
            <dd class="col-sm-9">
                <span class="badge bg-<?= match($apt['status']){
                    'завершено' => 'success',
                    'в работе' => 'warning text-dark',
                    'отменено' => 'danger',
                    default => 'secondary'
                } ?>"><?= $apt['status'] ?></span>
            </dd>
        </dl>
    </div>

    <?php if ($apt['status'] === 'запланировано' || $apt['status'] === 'в работе'): ?>
    <div class="bg-white p-4 rounded shadow-sm">
        <h3> Перенести запись</h3>
        <form method="POST" id="rescheduleForm">
            <?= csrf_field() ?>
            <input type="hidden" name="reschedule" value="1">
            
            <div class="mb-3">
                <label>Специалист</label>
                <select name="specialist_id" id="resSpec" class="form-select" required>
                    <?php foreach ($specialists as $s): ?>
                        <option value="<?= $s['specialist_id'] ?>" <?= ($apt['specialist_id'] ?? '') == $s['specialist_id'] ? 'selected' : '' ?>>
                            <?= escape($s['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label>Новая дата</label>
                <input type="date" name="date" id="resDate" class="form-control" min="<?= date('Y-m-d') ?>" required onchange="loadResSlots()">
            </div>
            
            <div class="mb-3">
                <label>Доступное время</label>
                <div id="resSlots" class="d-flex flex-wrap gap-2 mt-2"></div>
                <input type="hidden" name="new_datetime" id="resNewTime" required>
            </div>

            <button type="submit" class="btn btn-warning" onclick="return confirm('Подтвердить перенос?')">Перенести запись</button>
            <a href="appointments_list.php" class="btn btn-secondary">Назад к списку</a>
        </form>
    </div>
    <?php endif; ?>
</div>

<script>
function loadResSlots() {
    const sid = document.getElementById('resSpec').value;
    const date = document.getElementById('resDate').value;
    const container = document.getElementById('resSlots');
    const hidden = document.getElementById('resNewTime');
    if (!sid || !date) return;
    
    container.innerHTML = '<div class="spinner-border"></div>';
    fetch(`api/slots.php?specialist_id=${sid}&date=${date}&duration=60`)
        .then(r => r.json())
        .then(data => {
            container.innerHTML = '';
            if (!data.slots.length) { container.innerHTML = '<span class="text-danger">Нет слотов</span>'; return; }
            data.slots.forEach(t => {
                let btn = document.createElement('button');
                btn.type = 'button'; btn.className = 'btn btn-outline-primary slot-btn'; btn.innerText = t;
                btn.onclick = () => {
                    document.querySelectorAll('#resSlots .slot-btn').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    hidden.value = `${date} ${t}:00`;
                };
                container.appendChild(btn);
            });
        });
}
</script>

<?php require_once 'includes/footer.php'; ?>