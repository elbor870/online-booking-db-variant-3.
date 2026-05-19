-- phpMyAdmin SQL Dump
-- version 4.9.7
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Май 19 2026 г., 05:18
-- Версия сервера: 5.7.21-20-beget-5.7.21-20-1-log
-- Версия PHP: 5.6.40

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `u913430f_up`
--

-- --------------------------------------------------------

--
-- Структура таблицы `appointments`
--
-- Создание: Май 19 2026 г., 01:45
-- Последнее обновление: Май 19 2026 г., 01:53
--

DROP TABLE IF EXISTS `appointments`;
CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `appointment_datetime` datetime NOT NULL,
  `status` enum('запланировано','в работе','завершено','отменено') DEFAULT 'запланировано',
  `total_cost` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `client_id`, `car_id`, `service_id`, `appointment_datetime`, `status`, `total_cost`, `created_at`) VALUES
(1, 1, 1, 5, '2026-05-25 10:00:00', 'завершено', NULL, '2026-05-19 01:53:11'),
(2, 2, 3, 3, '2026-05-25 11:30:00', 'завершено', NULL, '2026-05-19 01:53:11'),
(3, 1, 2, 1, '2026-05-26 09:00:00', 'запланировано', NULL, '2026-05-19 01:53:11'),
(4, 3, 4, 6, '2026-05-27 14:00:00', 'завершено', NULL, '2026-05-19 01:53:11');

-- --------------------------------------------------------

--
-- Структура таблицы `appointment_parts`
--
-- Создание: Май 19 2026 г., 01:45
-- Последнее обновление: Май 19 2026 г., 01:53
--

DROP TABLE IF EXISTS `appointment_parts`;
CREATE TABLE `appointment_parts` (
  `appointment_id` int(11) NOT NULL,
  `part_id` int(11) NOT NULL,
  `quantity_used` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `appointment_parts`
--

INSERT INTO `appointment_parts` (`appointment_id`, `part_id`, `quantity_used`) VALUES
(1, 1, 1),
(1, 2, 1),
(2, 6, 20),
(4, 3, 1),
(4, 4, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `cars`
--
-- Создание: Май 19 2026 г., 01:45
-- Последнее обновление: Май 19 2026 г., 01:53
--

DROP TABLE IF EXISTS `cars`;
CREATE TABLE `cars` (
  `car_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `make` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `year` int(11) NOT NULL,
  `vin` varchar(17) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `cars`
--

INSERT INTO `cars` (`car_id`, `client_id`, `make`, `model`, `year`, `vin`) VALUES
(1, 1, 'Toyota', 'Camry', 2020, 'JTDBR32E050123456'),
(2, 1, 'VAZ', 'Granta', 2018, 'XTA219000J0123456'),
(3, 2, 'Hyundai', 'Solaris', 2021, 'Z94CT41DBMR012345'),
(4, 3, 'Ford', 'Focus', 2019, '1FADP3K29JL123456');

-- --------------------------------------------------------

--
-- Структура таблицы `clients`
--
-- Создание: Май 19 2026 г., 01:45
-- Последнее обновление: Май 19 2026 г., 01:53
--

DROP TABLE IF EXISTS `clients`;
CREATE TABLE `clients` (
  `client_id` int(11) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `patronymic` varchar(50) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `birth_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `clients`
--

INSERT INTO `clients` (`client_id`, `last_name`, `first_name`, `patronymic`, `phone`, `email`, `birth_date`, `created_at`) VALUES
(1, 'Иванов', 'Александр', 'Петрович', '+79161234567', 'ivanov@example.com', '1985-03-15', '2026-05-19 01:53:11'),
(2, 'Смирнова', 'Елена', 'Дмитриевна', '+79262345678', 'smirnova@example.com', '1990-07-22', '2026-05-19 01:53:11'),
(3, 'Козлов', 'Михаил', 'Андреевич', '+79033456789', 'kozlov@example.com', '1978-11-05', '2026-05-19 01:53:11');

-- --------------------------------------------------------

--
-- Структура таблицы `parts`
--
-- Создание: Май 19 2026 г., 01:45
-- Последнее обновление: Май 19 2026 г., 01:53
--

DROP TABLE IF EXISTS `parts`;
CREATE TABLE `parts` (
  `part_id` int(11) NOT NULL,
  `part_name` varchar(100) NOT NULL,
  `part_number` varchar(50) NOT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT '0',
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `parts`
--

INSERT INTO `parts` (`part_id`, `part_name`, `part_number`, `stock_quantity`, `price`) VALUES
(1, 'Моторное масло 5W-40', 'OIL-5W40-4L', 25, '2800.00'),
(2, 'Масляный фильтр', 'FILTER-OIL-001', 50, '450.00'),
(3, 'Тормозные колодки передние', 'BRAKE-FRT-001', 12, '2200.00'),
(4, 'Тормозные колодки задние', 'BRAKE-REAR-001', 8, '1800.00'),
(5, 'Шина летняя 205/55 R16', 'TIRE-SUM-205', 30, '4500.00'),
(6, 'Грузик балансировочный 10г', 'WEIGHT-10G', 200, '15.00'),
(7, 'Датчик кислорода', 'SENSOR-O2-001', 5, '3200.00');

-- --------------------------------------------------------

--
-- Структура таблицы `services`
--
-- Создание: Май 19 2026 г., 01:45
-- Последнее обновление: Май 19 2026 г., 01:53
--

DROP TABLE IF EXISTS `services`;
CREATE TABLE `services` (
  `service_id` int(11) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `category_id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration_minutes` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `services`
--

INSERT INTO `services` (`service_id`, `service_name`, `category_id`, `price`, `duration_minutes`) VALUES
(1, 'Компьютерная диагностика', 1, '2500.00', 45),
(2, 'Диагностика ходовой части', 1, '1500.00', 30),
(3, 'Балансировка колёс', 2, '800.00', 20),
(4, 'Замена шин', 2, '2000.00', 40),
(5, 'Замена масла', 3, '1200.00', 30),
(6, 'Замена тормозных колодок', 3, '3500.00', 60);

-- --------------------------------------------------------

--
-- Структура таблицы `service_categories`
--
-- Создание: Май 19 2026 г., 01:45
-- Последнее обновление: Май 19 2026 г., 01:53
--

DROP TABLE IF EXISTS `service_categories`;
CREATE TABLE `service_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` enum('диагностика','шиномонтаж','слесарные работы') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `service_categories`
--

INSERT INTO `service_categories` (`category_id`, `category_name`) VALUES
(1, 'диагностика'),
(2, 'шиномонтаж'),
(3, 'слесарные работы');

-- --------------------------------------------------------

--
-- Структура таблицы `service_parts`
--
-- Создание: Май 19 2026 г., 01:45
-- Последнее обновление: Май 19 2026 г., 01:53
--

DROP TABLE IF EXISTS `service_parts`;
CREATE TABLE `service_parts` (
  `service_id` int(11) NOT NULL,
  `part_id` int(11) NOT NULL,
  `required_quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `service_parts`
--

INSERT INTO `service_parts` (`service_id`, `part_id`, `required_quantity`) VALUES
(3, 6, 20),
(4, 5, 4),
(5, 1, 1),
(5, 2, 1),
(6, 3, 1),
(6, 4, 1);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD UNIQUE KEY `unique_car_slot` (`car_id`,`appointment_datetime`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `appointment_parts`
--
ALTER TABLE `appointment_parts`
  ADD PRIMARY KEY (`appointment_id`,`part_id`),
  ADD KEY `part_id` (`part_id`);

--
-- Индексы таблицы `cars`
--
ALTER TABLE `cars`
  ADD PRIMARY KEY (`car_id`),
  ADD UNIQUE KEY `vin` (`vin`),
  ADD KEY `idx_client_cars` (`client_id`);

--
-- Индексы таблицы `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`client_id`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Индексы таблицы `parts`
--
ALTER TABLE `parts`
  ADD PRIMARY KEY (`part_id`),
  ADD UNIQUE KEY `part_number` (`part_number`),
  ADD KEY `idx_part_number` (`part_number`);

--
-- Индексы таблицы `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`service_id`),
  ADD UNIQUE KEY `service_name` (`service_name`),
  ADD KEY `idx_category` (`category_id`);

--
-- Индексы таблицы `service_categories`
--
ALTER TABLE `service_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Индексы таблицы `service_parts`
--
ALTER TABLE `service_parts`
  ADD PRIMARY KEY (`service_id`,`part_id`),
  ADD KEY `part_id` (`part_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `cars`
--
ALTER TABLE `cars`
  MODIFY `car_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `clients`
--
ALTER TABLE `clients`
  MODIFY `client_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `parts`
--
ALTER TABLE `parts`
  MODIFY `part_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `service_categories`
--
ALTER TABLE `service_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`car_id`) REFERENCES `cars` (`car_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `appointment_parts`
--
ALTER TABLE `appointment_parts`
  ADD CONSTRAINT `appointment_parts_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `appointment_parts_ibfk_2` FOREIGN KEY (`part_id`) REFERENCES `parts` (`part_id`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `cars`
--
ALTER TABLE `cars`
  ADD CONSTRAINT `cars_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`category_id`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `service_parts`
--
ALTER TABLE `service_parts`
  ADD CONSTRAINT `service_parts_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `service_parts_ibfk_2` FOREIGN KEY (`part_id`) REFERENCES `parts` (`part_id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
