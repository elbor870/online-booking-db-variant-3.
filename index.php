<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// 1️⃣ ПОДКЛЮЧЕНИЕ ЗАВИСИМОСТЕЙ
require_once 'lib/helpers.php';
require_once 'config.php';
require_once 'src/Database.php';
require_once 'src/Repository/AbstractRepository.php';
require_once 'src/Repository/ClientRepository.php';
require_once 'src/Repository/ServiceRepository.php';
require_once 'src/Repository/CarRepository.php';

// 2️⃣ ИНИЦИАЛИЗАЦИЯ
$entity = $_GET['entity'] ?? 'client';
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;

$allowed = ['client', 'service', 'car'];
if (!in_array($entity, $allowed)) die('404: Справочник не найден');

$db = Database::getInstance(require 'config.php');
$pdo = $db->getConnection();
$repoClass = ucfirst($entity) . 'Repository';
$repo = new $repoClass($db->getConnection());

$errors = [];
$data = [];

// 3️⃣ ️ ОБРАБОТКА POST (СТРОГО ДО ЛЮБОГО HTML/HEADER!)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) die('Ошибка безопасности');

    if ($action === 'create' || $action === 'edit') {
        $data = $_POST;
        foreach (['csrf_token', 'id', '_token', 'submit'] as $k) unset($data[$k]);
        
        $validateFunc = 'validate_' . $entity;
        $errors = function_exists($validateFunc) ? $validateFunc($data) : [];

        if (empty($errors)) {
            try {
                if ($action === 'create') $repo->insert($data);
                else $repo->update($id, $data);
                
                flash('success', ucfirst($entity) . ' успешно сохранён');
                header("Location: index.php?entity=$entity&action=list");
                exit; //  ОБЯЗАТЕЛЬНО: прерываем выполнение после редиректа
            } catch (\Exception $e) {
                $errors['db'] = 'Ошибка БД: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'delete') {
        try {
            $repo->delete($id);
            flash('success', 'Запись удалена');
        } catch (\PDOException $e) {
            flash('danger', $e->getCode() == 23000 ? 'Удаление невозможно: есть связанные записи.' : 'Ошибка: ' . $e->getMessage());
        }
        header("Location: index.php?entity=$entity&action=list");
        exit; // ⛔ ОБЯЗАТЕЛЬНО
    }
}

// 4️ ПОДГОТОВКА ДАННЫХ (GET-ЛОГИКА)
if ($action === 'edit' || $action === 'delete') {
    $data = $repo->findById($id);
    if (!$data) die('Запись не найдена');
} elseif ($action === 'list') {
    $search = $_GET['search'] ?? '';
    $allowedCols = $repo->getAllowedSortColumns();
    $sort = $_GET['sort'] ?? $allowedCols[0];
    $dir = ($_GET['dir'] ?? '') === 'DESC' ? 'DESC' : 'ASC';
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = 10; $offset = ($page - 1) * $limit;
    if (!in_array($sort, $allowedCols, true)) $sort = $allowedCols[0];

    $where = $entity === 'client' && $search ? ['last_name' => $search] : [];
    $items = $repo->findAll($where, [], [$sort => $dir], $limit, $offset);
    $totalPages = ceil(count($repo->findAll()) / $limit);
    $data = compact('items', 'search', 'sort', 'dir', 'page', 'totalPages');
}

// 5️⃣ РЕНДЕРИНГ (HTML ВЫВОДИТСЯ ТОЛЬКО ЗДЕСЬ)
$pageTitle = ucfirst($entity) . ($action === 'list' ? 's' : ' - ' . $action);
require_once 'includes/header.php';

$view = ($action === 'create' || $action === 'edit') ? "views/{$entity}/form.php" : "views/{$entity}/{$action}.php";
if (!file_exists($view)) die("Представление не найдено: $view");
require_once $view;

require_once 'includes/footer.php';