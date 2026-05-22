<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'lib/helpers.php';
require_once 'config.php';
require_once 'src/Database.php';
require_once 'src/Repository/AbstractRepository.php';
require_once 'src/Repository/AppointmentRepository.php';

$db = Database::getInstance(require 'config.php');
$repo = new AppointmentRepository($db->getConnection());

$month = $_GET['month'] ?? date('Y-m');
$type = $_GET['type'] ?? 'revenue';

// 🟢 1. ОБРАБОТКА ЭКСПОРТА (СТРОГО ДО ЛЮБОГО HTML!)
if (isset($_GET['export'])) {
    // Отправляем заголовки для скачивания файла
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="report_'.$type.'_'.$month.'.csv"');
    
    $out = fopen('php://output', 'w');
    // BOM для корректного отображения кириллицы в Excel
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

    // Заголовки таблицы
    $headers = match($type) {
        'specialists' => ['Специалист', 'Записей', 'Выручка'],
        'canceled' => ['Дата', 'Отменено'],
        default => ['Дата', 'Записей', 'Выручка']
    };
    fputcsv($out, $headers);

    // Данные
    $data = match($type) {
        'specialists' => $repo->getSpecialistReport($month),
        'canceled' => $repo->getCanceledReport($month),
        default => $repo->getRevenueReport($month)
    };

    foreach ($data as $row) {
        $line = [
            $row['name'] ?? date('d.m.Y', strtotime($row['day'] ?? '')),
            $row['count'],
            isset($row['revenue']) ? $row['revenue'] : ''
        ];
        fputcsv($out, $line);
    }

    fclose($out);
    exit; //  ВАЖНО: Прерываем скрипт, чтобы не грузился HTML-шаблон
}

// 🟢 2. ПОДГОТОВКА ДАННЫХ ДЛЯ HTML
$data = match($type) {
    'specialists' => $repo->getSpecialistReport($month),
    'canceled' => $repo->getCanceledReport($month),
    default => $repo->getRevenueReport($month)
};

// 🟢 3. ПОДКЛЮЧЕНИЕ ШАБЛОНА (HTML ВЫВОДИТСЯ ЗДЕСЬ)
$pageTitle = 'Отчёты';
require_once 'includes/header.php';
?>

<div class="container py-4">
    <h1 class="mb-4">📊 Отчёты</h1>
    
    <form class="row g-2 mb-4" method="GET">
        <div class="col-md-3">
            <select name="type" class="form-select" onchange="this.form.submit()">
                <option value="revenue" <?= $type=='revenue'?'selected':'' ?>>Выручка по дням</option>
                <option value="specialists" <?= $type=='specialists'?'selected':'' ?>>Рейтинг специалистов</option>
                <option value="canceled" <?= $type=='canceled'?'selected':'' ?>>Отменённые записи</option>
            </select>
        </div>
        <div class="col-md-3">
            <input type="month" name="month" class="form-control" value="<?= escape($month) ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Показать</button>
        </div>
        <div class="col-md-2">
            <a href="?type=<?= $type ?>&month=<?= urlencode($month) ?>&export=1" class="btn btn-success w-100">📥 CSV</a>
        </div>
    </form>

    <div class="bg-white p-3 rounded shadow-sm">
        <?php if (empty($data)): ?>
            <p class="text-center text-muted py-4">Нет данных за выбранный период</p>
        <?php else: 
            $headers = match($type) {
                'specialists' => ['Специалист', 'Кол-во', 'Выручка'],
                'canceled' => ['Дата', 'Кол-во отмен'],
                default => ['Дата', 'Кол-во', 'Выручка']
            };
        ?>
        <table class="table table-striped mb-0">
            <thead><tr><?php foreach($headers as $h) echo "<th>$h</th>"; ?></tr></thead>
            <tbody>
                <?php foreach ($data as $row): ?>
                <tr>
                    <td><?= escape($row['name'] ?? date('d.m.Y', strtotime($row['day'] ?? ''))) ?></td>
                    <td class="text-center"><?= $row['count'] ?></td>
                    <td class="text-end"><?= isset($row['revenue']) ? number_format($row['revenue'], 2, '.', ' ') . ' ₽' : '—' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>