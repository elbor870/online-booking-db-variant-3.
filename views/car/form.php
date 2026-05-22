<?php
$isEdit = $isEdit ?? isset($data['car_id']);
$formTitle = $formTitle ?? ($isEdit ? 'Редактирование автомобиля' : 'Новый автомобиль');
$formAction = $formAction ?? ($isEdit ? 'edit' : 'create');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escape($formTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h2><?= escape($formTitle) ?></h2>
    <form method="POST" class="bg-white p-4 rounded shadow-sm mt-3" novalidate>
        <?= csrf_field() ?>
        
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= escape($data['car_id'] ?? '') ?>">
        <?php endif; ?>

        <div class="mb-3">
            <label>Клиент *</label>
            <select name="client_id" class="form-select <?= !empty($errors['client_id']) ? 'is-invalid' : '' ?>" required>
                <option value="">— Выберите клиента —</option>
                <?php
                // Загружаем список клиентов для dropdown
                $clients = $pdo->query("SELECT client_id, last_name, first_name FROM clients ORDER BY last_name")->fetchAll();
                foreach ($clients as $client):
                ?>
                    <option value="<?= $client['client_id'] ?>" 
                            <?= ($data['client_id'] ?? '') == $client['client_id'] ? 'selected' : '' ?>>
                        <?= escape($client['last_name'] . ' ' . $client['first_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['client_id'])): ?>
                <div class="invalid-feedback"><?= escape($errors['client_id']) ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label>Марка *</label>
            <input type="text" name="make" class="form-control <?= !empty($errors['make']) ? 'is-invalid' : '' ?>" 
                   value="<?= escape($data['make'] ?? '') ?>" required>
            <?php if (!empty($errors['make'])): ?>
                <div class="invalid-feedback"><?= escape($errors['make']) ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label>Модель *</label>
            <input type="text" name="model" class="form-control <?= !empty($errors['model']) ? 'is-invalid' : '' ?>" 
                   value="<?= escape($data['model'] ?? '') ?>" required>
            <?php if (!empty($errors['model'])): ?>
                <div class="invalid-feedback"><?= escape($errors['model']) ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label>Год выпуска *</label>
            <input type="number" name="year" class="form-control <?= !empty($errors['year']) ? 'is-invalid' : '' ?>" 
                   value="<?= escape($data['year'] ?? '') ?>" min="1950" max="<?= date('Y') + 1 ?>" required>
            <?php if (!empty($errors['year'])): ?>
                <div class="invalid-feedback"><?= escape($errors['year']) ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-4">
            <label>VIN (необязательно)</label>
            <input type="text" name="vin" class="form-control <?= !empty($errors['vin']) ? 'is-invalid' : '' ?>" 
                   value="<?= escape($data['vin'] ?? '') ?>" maxlength="17" pattern="[A-HJ-NPR-Z0-9]{17}">
            <div class="form-text">17 символов (латиница, без I, O, Q)</div>
            <?php if (!empty($errors['vin'])): ?>
                <div class="invalid-feedback"><?= escape($errors['vin']) ?></div>
            <?php endif; ?>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success"><?= $isEdit ? 'Сохранить изменения' : 'Создать' ?></button>
            <a href="index.php?entity=car&action=list" class="btn btn-secondary">Отмена</a>
        </div>
        <?php if (!empty($errors['db'])): ?>
            <div class="alert alert-danger mt-3"><?= escape($errors['db']) ?></div>
        <?php endif; ?>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>