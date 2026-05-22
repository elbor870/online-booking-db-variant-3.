\# 📄 Отчёт по практической работе: Проектирование и реализация пользовательского интерфейса. Часть 1 (Справочники)

\## 1. 🎯 Цель работы

Научиться проектировать и реализовывать веб-интерфейсы для управления справочными данными (клиенты, услуги, автомобили). Освоить паттерн Repository (DAL), CRUD-операции, серверную валидацию, защиту от основных веб-угроз и принципы разделения логики и представления (MVC).



\## 2. ✅ Выполненные задачи



\## 3. 🏗 Архитектура и стек технологий

* Backend: PHP 8.x, PDO, MySQL 5.7+
* Frontend: HTML5, CSS3, Bootstrap 5, Vanilla JS
* Архитектура: MVC, Repository Pattern, единая точка входа (index.php)
* Инфраструктура: Git, хостинг Beget, phpMyAdmin
* Структура проекта:



```text

/public\_html

├── config.php          # Настройки БД (в .gitignore)

├── index.php           # Маршрутизатор и контроллер

├── lib/helpers.php     # Валидация, CSRF, flash-сообщения

├── src/

│   ├── Database.php

│   ── Repository/

│       ├── AbstractRepository.php

│       ├── ClientRepository.php

│       ├── ServiceRepository.php

│       ── CarRepository.php

└── views/

&nbsp;   ├── client/

&nbsp;   ├── service/

&nbsp;   └── car/

```



\## 4. 🔑 Ключевые реализации

\### 4.1. Паттерн Repository и DAL

Базовый класс AbstractRepository инкапсулирует типовые операции:



```php

abstract class AbstractRepository {

&nbsp;   protected PDO $pdo;

&nbsp;   protected string $table;

&nbsp;   protected string $primaryKey;

&nbsp;   protected array $allowedSortColumns = \[];



&nbsp;   public function findAll(...): array;

&nbsp;   public function findById(int $id): ?array;

&nbsp;   public function insert(array $data): int;

&nbsp;   public function update(int $id, array $data): bool;

&nbsp;   public function delete(int $id): bool;

}

```



Конкретные репозитории только указывают таблицу, первичный ключ и разрешённые столбцы для сортировки. Прямые SQL-запросы в контроллерах и представлениях отсутствуют.



\### 4.2. Динамическая маршрутизация и валидация

index.php определяет сущность и действие через $\_GET, динамически подключает репозиторий и функцию валидации:



```php

$repoClass = ucfirst($entity) . 'Repository';

$repo = new $repoClass($pdo);

$validateFunc = 'validate\_' . $entity;

$errors = function\_exists($validateFunc) ? $validateFunc($\_POST) : \[];

```



\### 4.3. Безопасность и защита данных



\### 4.4. Интерфейс и UX

* Адаптивные таблицы Bootstrap с hover-эффектами и цветными бейджами статусов
* Формы с inline-валидацией (классы is-invalid/invalid-feedback)
* Пагинация и сортировка по клику на заголовки столбцов
* Flash-сообщения об успехе/ошибке через сессию
* Единый навигационный хедер (includes/header.php)



\## 5. Тестирование и результаты



\## 6. 📝 Выводы

В ходе первой части практической работы успешно спроектирован и реализован веб-интерфейс для управления тремя справочниками: клиентами, услугами и автомобилями. Освоены принципы паттерна Repository, динамической маршрутизации, серверной валидации и многоуровневой защиты веб-приложения. Архитектура проекта соответствует MVC, код модульный и расширяемый, интерфейс адаптирован под различные устройства. Полученные навыки и наработанные компоненты стали фундаментом для реализации бизнес-операций (онлайн-запись, управление статусами, отчёты) во второй части работы.



---



📂 Репозиторий: https://github.com/<ваш-username>/<имя-репозитория>

📅 Дата сдачи: 22.05.2026

👤 Выполнил: <Ваше ФИО>

