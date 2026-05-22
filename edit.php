<?php
// edit.php использует form.php, передавая флаг редактирования
$isEdit = true;
$formTitle = 'Редактирование клиента';
$formAction = 'edit';
require __DIR__ . '/form.php';