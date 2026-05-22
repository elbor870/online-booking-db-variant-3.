<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Услуги | Автосервис</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h1 class="mb-4">Справочник услуг</h1>
    <a href="index.php?entity=service&action=create" class="btn btn-primary mb-3">+ Добавить услугу</a>
    <?= flash() ?>

    <div class="table-responsive bg-white p-3 rounded shadow-sm">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Категория</th>
                <th><a href="?entity=service&sort=price&dir=<?= $dir === 'ASC' ? 'DESC' : 'ASC' ?>">Цена <?= $sort==='price' ? ($dir==='ASC'?'↑':'↓') : '' ?></a></th>
                <th>Длительность (мин)</th>
                <th class="text-end">Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $row): ?>
                <tr>
                    <td><?= escape($row['service_id']) ?></td>
                    <td><?= escape($row['service_name']) ?></td>
                    <td><?= escape($row['category_name'] ?? '—') ?></td>
                    <td><?= number_format($row['price'], 2, '.', ' ') ?> ₽</td>
                    <td><?= escape($row['duration_minutes']) ?></td>
                    <td class="text-end">
                        <a href="index.php?entity=service&action=edit&id=<?= $row['service_id'] ?>" class="btn btn-sm btn-outline-primary">✏️</a>
                        <a href="index.php?entity=service&action=delete&id=<?= $row['service_id'] ?>" class="btn btn-sm btn-outline-danger">🗑️</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <nav class="mt-3"><ul class="pagination justify-content-center">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                <a class="page-link" href="?entity=service&action=list&page=<?= $i ?>&sort=<?= $sort ?>&dir=<?= $dir ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
    </ul></nav>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>