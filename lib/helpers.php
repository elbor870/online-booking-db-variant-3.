<?php
// lib/helpers.php
session_start();

function escape(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function csrf_field(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
}

function verify_csrf(): bool {
    return isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

function flash(string $type = '', string $msg = ''): void {
    if ($type && $msg) {
        $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
    } elseif (!empty($_SESSION['flash'])) {
        echo '<div class="alert alert-' . escape($_SESSION['flash']['type']) . ' alert-dismissible fade show mt-3" role="alert">';
        echo escape($_SESSION['flash']['msg']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        unset($_SESSION['flash']);
    }
}

// Валидация для Клиентов (расширяется для других сущностей)
function validate_client(array $data): array {
    $errors = [];
    if (trim($data['last_name'] ?? '') === '') $errors['last_name'] = 'Фамилия обязательна';
    if (trim($data['first_name'] ?? '') === '') $errors['first_name'] = 'Имя обязательно';
    
    $phone = $data['phone'] ?? '';
    if (!preg_match('/^\+?[0-9\s\-]{7,20}$/', $phone)) $errors['phone'] = 'Некорректный формат телефона';
    
    $email = $data['email'] ?? '';
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Некорректный email';
    
    $birth = $data['birth_date'] ?? '';
    if ($birth && strtotime($birth) > strtotime('today')) $errors['birth_date'] = 'Дата не может быть в будущем';
    if ($birth && (new DateTime())->diff(new DateTime($birth))->y < 18) $errors['birth_date'] = 'Клиенту должно быть ≥18 лет';
    
    return $errors;
}

/**
 * Валидация для услуг
 */
function validate_service(array $data): array {
    $errors = [];
    
    if (trim($data['service_name'] ?? '') === '') {
        $errors['service_name'] = 'Название услуги обязательно';
    }
    
    if (empty($data['category_id'])) {
        $errors['category_id'] = 'Выберите категорию';
    }
    
    if (!isset($data['price']) || $data['price'] === '' || $data['price'] <= 0) {
        $errors['price'] = 'Цена должна быть больше 0';
    }
    
    if (!isset($data['duration_minutes']) || $data['duration_minutes'] === '' || $data['duration_minutes'] <= 0) {
        $errors['duration_minutes'] = 'Длительность должна быть больше 0';
    }
    
    return $errors;
}

/**
 * Валидация для автомобилей
 */
function validate_car(array $data): array {
    $errors = [];
    
    if (empty($data['client_id'])) {
        $errors['client_id'] = 'Выберите клиента';
    }
    
    if (trim($data['make'] ?? '') === '') {
        $errors['make'] = 'Марка обязательна';
    }
    
    if (trim($data['model'] ?? '') === '') {
        $errors['model'] = 'Модель обязательна';
    }
    
    $year = (int)($data['year'] ?? 0);
    $currentYear = (int)date('Y');
    
    if ($year < 1950 || $year > $currentYear + 1) {
        $errors['year'] = 'Год должен быть от 1950 до ' . ($currentYear + 1);
    }
    
    $vin = trim($data['vin'] ?? '');
    if ($vin !== '' && strlen($vin) !== 17) {
        $errors['vin'] = 'VIN должен содержать 17 символов';
    }
    
    return $errors;
}