<?php
$pageTitle = $pageTitle ?? 'Система записи';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | Автосервис</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">🚗 Автосервис</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">📖 Справочники</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="index.php?entity=client&action=list">Клиенты</a></li>
                        <li><a class="dropdown-item" href="index.php?entity=service&action=list">Услуги</a></li>
                        <li><a class="dropdown-item" href="index.php?entity=car&action=list">Автомобили</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['REQUEST_URI']) === 'booking.php' ? 'active' : '' ?>" href="booking.php">📅 Онлайн-запись</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'appointments') ? 'active' : '' ?>" href="appointments_list.php">📋 Управление</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'reports') ? 'active' : '' ?>" href="reports.php">📊 Отчёты</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">