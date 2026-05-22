<?php
declare(strict_types=1);

class AppointmentRepository extends AbstractRepository
{
    protected string $table = 'appointments';
    protected string $primaryKey = 'appointment_id';
    protected array $allowedSortColumns = ['appointment_id', 'appointment_datetime', 'status', 'client_id', 'specialist_id'];

    /**
     * Получение доступных слотов с учётом специалиста, даты и длительности услуги
     */
    public function getAvailableSlots(int $specialistId, string $date, int $serviceDuration = 60): array
    {
        $step = 30; // шаг 30 минут
        $startHour = 9; $endHour = 18; $lunchStart = 13; $lunchEnd = 14;
        $slots = [];
        
        for ($h = $startHour; $h < $endHour; $h++) {
            for ($m = 0; $m < 60; $m += $step) {
                // Пропускаем обед 13:00-14:00
                if ($h == $lunchStart && $m == 0) continue;
                
                $time = sprintf('%02d:%02d', $h, $m);
                $slots[] = $time;
            }
        }

        // Занятые интервалы у конкретного специалиста
        $stmt = $this->pdo->prepare("
            SELECT DATE_FORMAT(appointment_datetime, '%H:%i') as slot 
            FROM appointments 
            WHERE specialist_id = ? 
              AND DATE(appointment_datetime) = ? 
              AND status != 'отменено'
        ");
        $stmt->execute([$specialistId, $date]);
        $busySlots = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        return array_values(array_diff($slots, $busySlots));
    }

    /**
     * Создание записи с конкурентной защитой (Optimistic Locking)
     */
    public function createAppointment(array $data): int
    {
        $this->pdo->beginTransaction();
        try {
            //  Повторная проверка слота перед вставкой
            $check = $this->pdo->prepare("
                SELECT COUNT(*) FROM appointments 
                WHERE specialist_id = ? AND appointment_datetime = ? AND status != 'отменено'
            ");
            $check->execute([$data['specialist_id'], $data['appointment_datetime']]);
            if ($check->fetchColumn() > 0) {
                throw new \Exception("К сожалению, выбранное время только что занято другим клиентом.");
            }

            $sql = "INSERT INTO appointments (client_id, car_id, service_id, specialist_id, appointment_datetime, status) 
                    VALUES (?, ?, ?, ?, ?, 'запланировано')";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['client_id'],
                $data['car_id'],
                $data['service_id'],
                $data['specialist_id'],
                $data['appointment_datetime']
            ]);

            $this->pdo->commit();
            return (int)$this->pdo->lastInsertId();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Смена статуса с проверкой допустимости перехода
     */
    public function changeStatus(int $id, string $newStatus): bool
    {
        $allowedTransitions = [
            'запланировано' => ['в работе', 'отменено'],
            'в работе' => ['завершено', 'отменено'],
            'отменено' => [],
            'завершено' => []
        ];

        $current = $this->findById($id);
        if (!$current) throw new \Exception("Запись не найдена");
        
        $currentStatus = $current['status'];
        if (!in_array($newStatus, $allowedTransitions[$currentStatus] ?? [], true)) {
            throw new \Exception("Недопустимый переход статуса: {$currentStatus} → {$newStatus}");
        }

        return $this->update($id, ['status' => $newStatus]);
    }

    /**
     * Перенос записи на новое время
     */
    public function reschedule(int $appointmentId, int $specialistId, string $newDatetime): bool
    {
        $this->pdo->beginTransaction();
        try {
            // Проверка доступности нового слота
            $check = $this->pdo->prepare("
                SELECT COUNT(*) FROM appointments 
                WHERE specialist_id = ? AND appointment_datetime = ? AND status != 'отменено' AND appointment_id != ?
            ");
            $check->execute([$specialistId, $newDatetime, $appointmentId]);
            if ($check->fetchColumn() > 0) {
                throw new \Exception("Новое время уже занято. Выберите другой слот.");
            }

            $stmt = $this->pdo->prepare("
                UPDATE appointments 
                SET appointment_datetime = ?, status = 'запланировано' 
                WHERE appointment_id = ?
            ");
            $stmt->execute([$newDatetime, $appointmentId]);

            // Логирование (если таблица appointment_log существует)
            try {
                $this->pdo->exec("INSERT INTO appointment_log (appointment_id, old_datetime, new_datetime, changed_at) 
                                  VALUES ($appointmentId, (SELECT appointment_datetime FROM appointments WHERE appointment_id=$appointmentId), '$newDatetime', NOW())");
            } catch (\Exception $e) {
                // Игнорируем ошибку лога, если таблица ещё не создана
            }
            
            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Список записей с фильтрами (для appointments_list.php)
     */
    public function getAppointmentsWithFilters(array $filters = [], int $limit = 20, int $offset = 0): array
{
    $sql = "SELECT a.*, c.last_name, c.first_name, car.make, car.model, 
                   s.service_name, s.price, s.duration_minutes, 
                   sp.name as specialist_name
            FROM appointments a
            JOIN clients c ON a.client_id = c.client_id
            JOIN cars car ON a.car_id = car.car_id
            JOIN services s ON a.service_id = s.service_id
            LEFT JOIN specialists sp ON a.specialist_id = sp.specialist_id
            WHERE 1=1";
    
    $params = [];
    if (!empty($filters['status'])) {
        $sql .= " AND a.status = ?";
        $params[] = $filters['status'];
    }
    if (!empty($filters['date_from'])) {
        $sql .= " AND DATE(a.appointment_datetime) >= ?";
        $params[] = $filters['date_from'];
    }
    if (!empty($filters['specialist_id'])) {
        $sql .= " AND a.specialist_id = ?";
        $params[] = $filters['specialist_id'];
    }

    $sql .= " ORDER BY a.appointment_datetime DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

    /**
     * Общее количество записей для пагинации
     */
   public function countAppointments(array $filters = []): int
{
    $sql = "SELECT COUNT(*) FROM appointments WHERE 1=1";
    $params = [];
    
    if (!empty($filters['status'])) {
        $sql .= " AND status = ?";
        $params[] = $filters['status'];
    }
    if (!empty($filters['date_from'])) {
        $sql .= " AND DATE(appointment_datetime) >= ?";
        $params[] = $filters['date_from'];
    }
    if (!empty($filters['specialist_id'])) {
        $sql .= " AND specialist_id = ?";
        $params[] = $filters['specialist_id'];
    }

    // ✅ Правильный способ: prepare -> execute -> fetchColumn
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn();
}

    /**
     * Отчёт 1: Выручка и записи по дням
     */
    public function getRevenueReport(string $month): array
    {
        $stmt = $this->pdo->prepare("
            SELECT DATE(a.appointment_datetime) as day, 
                   COUNT(*) as count, 
                   SUM(s.price) as revenue
            FROM appointments a
            JOIN services s ON a.service_id = s.service_id
            WHERE a.status = 'завершено' 
              AND DATE_FORMAT(a.appointment_datetime, '%Y-%m') = ?
            GROUP BY DATE(a.appointment_datetime)
            ORDER BY day ASC
        ");
        $stmt->execute([$month]);
        return $stmt->fetchAll();
    }

    /**
     * Отчёт 2: Рейтинг специалистов
     */
    public function getSpecialistReport(string $month): array
    {
        $stmt = $this->pdo->prepare("
            SELECT sp.name, COUNT(a.appointment_id) as count, SUM(s.price) as revenue
            FROM appointments a
            JOIN specialists sp ON a.specialist_id = sp.specialist_id
            JOIN services s ON a.service_id = s.service_id
            WHERE a.status = 'завершено' AND DATE_FORMAT(a.appointment_datetime, '%Y-%m') = ?
            GROUP BY sp.specialist_id, sp.name ORDER BY revenue DESC
        ");
        $stmt->execute([$month]);
        return $stmt->fetchAll();
    }

    /**
     * Отчёт 3: Отменённые записи
     */
    /**
 * Отчёт 3: Отменённые записи (только количество по дням, т.к. причина не хранится)
 */
public function getCanceledReport(string $month): array
{
    $stmt = $this->pdo->prepare("
        SELECT DATE(appointment_datetime) as day, COUNT(*) as count
        FROM appointments
        WHERE status = 'отменено' 
          AND DATE_FORMAT(appointment_datetime, '%Y-%m') = ?
        GROUP BY DATE(appointment_datetime) 
        ORDER BY day ASC
    ");
    $stmt->execute([$month]);
    return $stmt->fetchAll();
}
    
    /**
 * Получение записи с полными данными (клиент, авто, услуга, специалист)
 */
public function getAppointmentWithDetails(int $id): ?array
{
    $sql = "SELECT a.*, 
                   c.last_name, c.first_name, c.patronymic, c.phone, c.email,
                   car.make, car.model, car.year, car.vin,
                   s.service_name, s.price, s.duration_minutes,
                   sp.name as specialist_name, sp.specialty
            FROM appointments a
            LEFT JOIN clients c ON a.client_id = c.client_id
            LEFT JOIN cars car ON a.car_id = car.car_id
            LEFT JOIN services s ON a.service_id = s.service_id
            LEFT JOIN specialists sp ON a.specialist_id = sp.specialist_id
            WHERE a.appointment_id = ?";
    
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}
    
}