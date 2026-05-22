<?php
declare(strict_types=1);

/**
 * Репозиторий для работы с таблицей автомобилей.
 * Наследует базовый CRUD от AbstractRepository и добавляет специфичные методы.
 */
class CarRepository extends AbstractRepository
{
    protected string $table = 'cars';
    protected string $primaryKey = 'car_id';
    protected array $allowedSortColumns = ['car_id', 'make', 'model', 'year', 'client_id'];

    /**
     * Поиск автомобиля по VIN-номеру
     */
    public function findByVin(string $vin): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE vin = ?");
        $stmt->execute([$vin]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Получение всех автомобилей конкретного клиента
     */
    public function getCarsByClientId(int $clientId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->table} 
            WHERE client_id = ? 
            ORDER BY year DESC, make ASC
        ");
        $stmt->execute([$clientId]);
        return $stmt->fetchAll();
    }

    /**
     * Переопределение insert для добавления бизнес-валидации (опционально)
     * В данном случае используем родительский метод, т.к. уникальность VIN контролируется БД.
     */
}