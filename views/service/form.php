<?php
$isEdit = $isEdit ?? isset($data['service_id']);
$formTitle = $formTitle ?? ($isEdit ? 'Редактирование услуги' : 'Новая услуга');
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
            <input type="hidden" name="id" value="<?= escape($data['service_id'] ?? '') ?>">
        <?php endif; ?>

        <div class="mb-3">
            <label>Название услуги *</label>
            <input type="text" name="service_name" class="form-control <?= !empty($errors['service_name']) ? 'is-invalid' : '' ?>" 
                   value="<?= escape($data['service_name'] ?? '') ?>" required>
            <?php if (!empty($errors['service_name'])): ?>
                <div class="invalid-feedback"><?= escape($errors['service_name']) ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label>Категория *</label>
            <select name="category_id" class="form-select <?= !empty($errors['category_id']) ? 'is-invalid' : '' ?>" required>
                <option value="">— Выберите категорию —</option>
                <option value="1" <?= ($data['category_id'] ?? '') == 1 ? 'selected' : '' ?>>Диагностика</option>
                <option value="2" <?= ($data['category_id'] ?? '') == 2 ? 'selected' : '' ?>>Шиномонтаж</option>
                <option value="3" <?= ($data['category_id'] ?? '') == 3 ? 'selected' : '' ?>>Слесарные работы</option>
            </select>
            <?php if (!empty($errors['category_id'])): ?>
                <div class="invalid-feedback"><?= escape($errors['category_id']) ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label>Цена (₽) *</label>
            <input type="number" name="price" class="form-control <?= !empty($errors['price']) ? 'is-invalid' : '' ?>" 
                   value="<?= escape($data['price'] ?? '') ?>" step="0.01" min="0" required>
            <?php if (!empty($errors['price'])): ?>
                <div class="invalid-feedback"><?= escape($errors['price']) ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-4">
            <label>Длительность (минут) *</label>
            <input type="number" name="duration_minutes" class="form-control <?= !empty($errors['duration_minutes']) ? 'is-invalid' : '' ?>" 
                   value="<?= escape($data['duration_minutes'] ?? '') ?>" min="1" required>
            <?php if (!empty($errors['duration_minutes'])): ?>
                <div class="invalid-feedback"><?= escape($errors['duration_minutes']) ?></div>
            <?php endif; ?>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success"><?= $isEdit ? 'Сохранить изменения' : 'Создать' ?></button>
            <a href="index.php?entity=service&action=list" class="btn btn-secondary">Отмена</a>
        </div>
        <?php if (!empty($errors['db'])): ?>
            <div class="alert alert-danger mt-3"><?= escape($errors['db']) ?></div>
        <?php endif; ?>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>