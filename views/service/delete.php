<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Подтверждение удаления</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="bg-white p-4 rounded shadow text-center">
        <h3>Удалить запись?</h3>
        <p class="text-muted">Вы действительно хотите удалить клиента <strong><?= escape($data['last_name'] . ' ' . $data['first_name']) ?></strong>?</p>
        <form method="POST">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-danger me-2">Да, удалить</button>
            <a href="index.php?entity=client&action=list" class="btn btn-secondary">Отмена</a>
        </form>
    </div>
</div>
</body>
</html>