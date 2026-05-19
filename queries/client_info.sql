-- Вывод всех записей с информацией о клиенте, авто, услуге и категории
SELECT
    CONCAT(c.last_name, ' ', c.first_name) AS client_name,
    CONCAT(car.make, ' ', car.model, ' (', car.year, ')') AS vehicle,
    sc.category_name AS service_category,
    s.service_name,
    a.appointment_datetime,
    a.status,
    s.price AS service_price
FROM appointments a
JOIN clients c ON a.client_id = c.client_id
JOIN cars car ON a.car_id = car.car_id
JOIN services s ON a.service_id = s.service_id
JOIN service_categories sc ON s.category_id = sc.category_id
ORDER BY a.appointment_datetime;
