<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1️⃣ ЗАВИСИМОСТИ
require_once 'lib/helpers.php';
require_once 'config.php';
require_once 'src/Database.php';
require_once 'src/Repository/AbstractRepository.php';
require_once 'src/Repository/AppointmentRepository.php';

$db = Database::getInstance(require 'config.php');
$pdo = $db->getConnection();
$repo = new AppointmentRepository($pdo);

// 2️⃣ ОБРАБОТКА POST-ЗАПРОСОВ (СТРОГО ДО ЛЮБОГО HTML!)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_status'])) {
    if (!verify_csrf()) die('Ошибка безопасности: недействительный токен');
    
    $id = (int)$_POST['id'];
    $status = $_POST['status'];
    
    try {
        $repo->changeStatus($id, $status);
        flash('success', 'Статус записи успешно обновлён');
    } catch (Exception $e) {
        flash('danger', $e->getMessage());
    }
    
    // Редирект ДО вывода HTML
    header("Location: appointments_list.php");
    exit; // Прерываем скрипт, чтобы не рендерить форму повторно
}

// 3️⃣ ПОДГОТОВКА ДАННЫХ ДЛЯ ВЫВОДА
$statusFilter = $_GET['status'] ?? '';
$dateFilter   = $_GET['date_from'] ?? '';
$specFilter   = $_GET['specialist_id'] ?? '';
$page         = max(1, (int)($_GET['page'] ?? 1));
$limit        = 20; // ТЗ: 20 записей на страницу
$offset       = ($page - 1) * $limit;

$filters = [];
if ($statusFilter) $filters['status'] = $statusFilter;
if ($dateFilter)   $filters['date_from'] = $dateFilter;
if ($specFilter)   $filters['specialist_id'] = $specFilter;

$appointments = $repo->getAppointmentsWithFilters($filters, $limit, $offset);
$totalCount   = $repo->countAppointments($filters);
$totalPages   = ceil($totalCount / $limit);

// 4️⃣ ПОДКЛЮЧЕНИЕ ШАБЛОНА (HTML ВЫВОДИТСЯ ТОЛЬКО ЗДЕСЬ)
$pageTitle = 'Управление записями';
require_once 'includes/header.php';
?>

<div class="container py-4">
    <h1 class="mb-4">📋 Управление записями</h1>
    <?= flash() ?>

    <form class="row g-2 mb-4" method="GET">
        <div class="col-md-3">
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">Все статусы</option>
                <option value="запланировано" <?= $statusFilter==='запланировано'?'selected':'' ?>>Запланировано</option>
                <option value="в работе" <?= $statusFilter==='в работе'?'selected':'' ?>>В работе</option>
                <option value="завершено" <?= $statusFilter==='завершено'?'selected':'' ?>>Завершено</option>
                <option value="отменено" <?= $statusFilter==='отменено'?'selected':'' ?>>Отменено</option>
            </select>
        </div>
        <div class="col-md-3">
            <input type="date" name="date_from" class="form-control" value="<?= escape($dateFilter) ?>" onchange="this.form.submit()">
        </div>
        <div class="col-md-2">
            <a href="appointments_list.php" class="btn btn-outline-secondary w-100">Сбросить</a>
        </div>
    </form>

    <div class="table-responsive bg-white p-3 rounded shadow-sm">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
            <tr>
                <th>Дата и время</th>
                <th>Клиент</th>
                <th>Автомобиль</th>
                <th>Услуга</th>
                <th>Статус</th>
                <th class="text-end">Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($appointments)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Записей не найдено</td></tr>
            <?php else: foreach ($appointments as $a): ?>
                <tr>
                    <td><?= date('d.m.Y H:i', strtotime($a['appointment_datetime'])) ?></td>
                    <td><?= escape($a['last_name'] . ' ' . $a['first_name']) ?></td>
                    <td><?= escape($a['make'] . ' ' . $a['model']) ?></td>
                    <td><?= escape($a['service_name']) ?></td>
                    <td>
                        <?php 
                        $badge = match($a['status']) {
                            'завершено' => 'success',
                            'в работе' => 'warning text-dark',
                            'отменено' => 'danger',
                            default => 'secondary'
                        };
                        ?>
                        <span class="badge bg-<?= $badge ?>"><?= escape($a['status']) ?></span>
                    </td>
                    <td class="text-end">
                        <a href="appointment_view.php?id=<?= $a['appointment_id'] ?>" class="btn btn-sm btn-outline-info">Просмотр/Перенос</a>
                        <?php if ($a['status'] === 'запланировано'): ?>
                            <form method="POST" class="d-inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $a['appointment_id'] ?>">
                                <input type="hidden" name="status" value="в работе">
                                <button type="submit" name="change_status" class="btn btn-sm btn-outline-primary" onclick="return confirm('Начать работу?')">В работу</button>
                            </form>
                            <form method="POST" class="d-inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $a['appointment_id'] ?>">
                                <input type="hidden" name="status" value="отменено">
                                <button type="submit" name="change_status" class="btn btn-sm btn-outline-danger" onclick="return confirm('Отменить запись?')">Отменить</button>
                            </form>
                        <?php elseif ($a['status'] === 'в работе'): ?>
                            <form method="POST" class="d-inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $a['appointment_id'] ?>">
                                <input type="hidden" name="status" value="завершено">
                                <button type="submit" name="change_status" class="btn btn-sm btn-outline-success" onclick="return confirm('Завершить запись?')">Завершить</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Пагинация -->
    <?php if ($totalPages > 1): ?>
    <nav class="mt-3"><ul class="pagination justify-content-center">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
    </ul></nav>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>