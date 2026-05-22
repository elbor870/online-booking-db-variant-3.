<!DOCTYPE html>
<?php
// Если файлы create.php и edit.php не передали переменные
$isEdit = $isEdit ?? isset($data['client_id']);
$formTitle = $formTitle ?? ($isEdit ? 'Редактирование клиента' : 'Новый клиент');
$formAction = $formAction ?? ($isEdit ? 'edit' : 'create');
?>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Форма клиента</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h2><?= isset($data['client_id']) ? 'Редактирование' : 'Новый клиент' ?></h2>
    <form method="POST" class="bg-white p-4 rounded shadow-sm mt-3" novalidate>
    <?= csrf_field() ?>
    
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= escape($data['client_id'] ?? '') ?>">
    <?php endif; ?>
    
    <!-- остальные поля -->
    
    <button type="submit" class="btn btn-success">
        <?= $isEdit ? 'Сохранить изменения' : 'Создать' ?>
    </button>
        <div class="mb-3">
            <label>Фамилия *</label>
            <input type="text" name="last_name" class="form-control <?= !empty($errors['last_name']) ? 'is-invalid' : '' ?>" value="<?= escape($data['last_name'] ?? '') ?>" required>
            <?php if (!empty($errors['last_name'])): ?><div class="invalid-feedback"><?= $errors['last_name'] ?></div><?php endif; ?>
        </div>
        <div class="mb-3">
            <label>Имя *</label>
            <input type="text" name="first_name" class="form-control <?= !empty($errors['first_name']) ? 'is-invalid' : '' ?>" value="<?= escape($data['first_name'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label>Телефон *</label>
            <input type="tel" name="phone" class="form-control <?= !empty($errors['phone']) ? 'is-invalid' : '' ?>" value="<?= escape($data['phone'] ?? '') ?>" pattern="\+?[0-9\s\-]{7,20}" required>
            <?php if (!empty($errors['phone'])): ?><div class="invalid-feedback"><?= $errors['phone'] ?></div><?php endif; ?>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control <?= !empty($errors['email']) ? 'is-invalid' : '' ?>" value="<?= escape($data['email'] ?? '') ?>">
            <?php if (!empty($errors['email'])): ?><div class="invalid-feedback"><?= $errors['email'] ?></div><?php endif; ?>
        </div>
        <div class="mb-4">
            <label>Дата рождения *</label>
            <input type="date" name="birth_date" class="form-control <?= !empty($errors['birth_date']) ? 'is-invalid' : '' ?>" value="<?= escape($data['birth_date'] ?? '') ?>" required>
            <?php if (!empty($errors['birth_date'])): ?><div class="invalid-feedback"><?= $errors['birth_date'] ?></div><?php endif; ?>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success"><?= isset($data['client_id']) ? 'Сохранить изменения' : 'Создать' ?></button>
            <a href="index.php?entity=client&action=list" class="btn btn-secondary">Отмена</a>
        </div>
        <?php if (!empty($errors['db'])): ?>
            <div class="alert alert-danger mt-3"><?= escape($errors['db']) ?></div>
        <?php endif; ?>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>