-- 💰 Выручка за выбранный день (с учётом только завершённых записей)
-- Замените '2026-05-25' на нужную дату или используйте CURDATE()

SELECT
    DATE(a.appointment_datetime) AS report_date,
    COUNT(DISTINCT a.appointment_id) AS total_appointments,
    SUM(s.price) AS services_revenue,
    COALESCE(SUM(p.price * ap.quantity_used), 0) AS parts_revenue,
    SUM(s.price) + COALESCE(SUM(p.price * ap.quantity_used), 0) AS total_daily_revenue
FROM appointments a
JOIN services s ON a.service_id = s.service_id
LEFT JOIN appointment_parts ap ON a.appointment_id = ap.appointment_id
LEFT JOIN parts p ON ap.part_id = p.part_id
WHERE a.status = 'завершено'
  AND DATE(a.appointment_datetime) = '2026-05-25'  -- <-- целевая дата
GROUP BY DATE(a.appointment_datetime);
