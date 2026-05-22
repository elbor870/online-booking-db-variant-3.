<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Клиенты | Автосервис</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h1 class="mb-4">Справочник клиентов</h1>
    <a href="index.php?entity=client&action=create" class="btn btn-primary mb-3">+ Добавить клиента</a>
    <?= flash() ?>

    <form class="row g-2 mb-3" method="GET">
        <input type="hidden" name="entity" value="client">
        <input type="hidden" name="action" value="list">
        <div class="col-md-6">
            <input type="text" name="search" class="form-control" placeholder="Поиск по фамилии..." value="<?= escape($search) ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-outline-secondary w-100">Найти</button>
        </div>
    </form>

    <div class="table-responsive bg-white p-3 rounded shadow-sm">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
            <tr>
                <th><a href="?entity=client&sort=last_name&dir=<?= $dir === 'ASC' ? 'DESC' : 'ASC' ?>">Фамилия <?= $sort==='last_name' ? ($dir==='ASC'?'↑':'↓') : '' ?></a></th>
                <th>Имя</th>
                <th>Телефон</th>
                <th>Email</th>
                <th>Дата рождения</th>
                <th class="text-end">Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $row): ?>
                <tr>
                    <td><?= escape($row['last_name']) ?></td>
                    <td><?= escape($row['first_name']) ?></td>
                    <td><?= escape($row['phone']) ?></td>
                    <td><?= escape($row['email']) ?></td>
                    <td><?= escape($row['birth_date']) ?></td>
                    <td class="text-end">
                        <a href="index.php?entity=client&action=edit&id=<?= $row['client_id'] ?>" class="btn btn-sm btn-outline-primary">✏️</a>
                        <a href="index.php?entity=client&action=delete&id=<?= $row['client_id'] ?>" class="btn btn-sm btn-outline-danger">🗑️</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Пагинация -->
    <?php if ($totalPages > 1): ?>
    <nav class="mt-3"><ul class="pagination justify-content-center">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                <a class="page-link" href="?entity=client&action=list&page=<?= $i ?>&sort=<?= $sort ?>&dir=<?= $dir ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
    </ul></nav>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>