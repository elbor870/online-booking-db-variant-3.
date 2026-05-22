<?php
// create.php использует form.php, передавая флаг создания
$isEdit = false;
$formTitle = 'Новый клиент';
$formAction = 'create';
require __DIR__ . '/form.php';