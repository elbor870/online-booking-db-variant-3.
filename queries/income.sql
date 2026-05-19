-- Выручка по категориям услуг за завершённые записи (только категории с выручкой > 3000₽)
SELECT
    sc.category_name,
    COUNT(a.appointment_id) AS completed_count,
    SUM(s.price) AS total_revenue
FROM appointments a
JOIN services s ON a.service_id = s.service_id
JOIN service_categories sc ON s.category_id = sc.category_id
WHERE a.status = 'завершено'
GROUP BY sc.category_id, sc.category_name
HAVING total_revenue > 3000
ORDER BY total_revenue DESC;
