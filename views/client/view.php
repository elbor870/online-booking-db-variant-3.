<?php
// поддержка обоих способов передачи данных
$client = $client ?? ($data['client'] ?? $data);
$cars = $cars ?? ($data['cars'] ?? []);
$appointments = $appointments ?? ($data['appointments'] ?? []);
?>
<div class="container py-4">
    <h1 class="mb-4">Карточка клиента</h1>

    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">Информация о клиенте</div>
        <div class="card-body">
            <h5><?= escape($client['last_name'] . ' ' . $client['first_name'] . ' ' . ($client['patronymic'] ?? '')) ?></h5>
            <p class="mb-1"><strong>Телефон:</strong> <?= escape($client['phone']) ?></p>
            <p class="mb-1"><strong>Email:</strong> <?= escape($client['email']) ?></p>
            <p class="mb-0"><strong>Дата рождения:</strong> <?= escape($client['birth_date']) ?></p>
        </div>
    </div>

    <h4 class="mb-3">Автомобили клиента</h4>
    <?php if (empty($cars)): ?>
        <div class="alert alert-info">У клиента нет автомобилей</div>
    <?php else: ?>
        <div class="table-responsive bg-white p-3 rounded shadow-sm mb-4">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                <tr>
                    <th>Марка</th>
                    <th>Модель</th>
                    <th>Год выпуска</th>
                    <th>VIN</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($cars as $car): ?>
                    <tr>
                        <td><?= escape($car['make']) ?></td>
                        <td><?= escape($car['model']) ?></td>
                        <td><?= escape($car['year']) ?></td>
                        <td><?= escape($car['vin'] ?? '—') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <h4 class="mb-3">История записей</h4>
    <?php if (empty($appointments)): ?>
        <div class="alert alert-info">У клиента нет записей</div>
    <?php else: ?>
        <div class="table-responsive bg-white p-3 rounded shadow-sm">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                <tr>
                    <th>Дата и время</th>
                    <th>Автомобиль</th>
                    <th>Услуга</th>
                    <th>Статус</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($appointments as $a): ?>
                    <tr>
                        <td><?= escape($a['appointment_datetime']) ?></td>
                        <td><?= escape($a['make'] . ' ' . $a['model']) ?></td>
                        <td><?= escape($a['service_name']) ?></td>
                        <td><?= escape($a['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <a href="index.php?entity=client&action=list" class="btn btn-secondary mt-3">Назад к списку</a>
</div>
