<?php
declare(strict_types=1);

class ServiceRepository extends AbstractRepository
{
    protected string $table = 'services';
    protected string $primaryKey = 'service_id';
    protected array $allowedSortColumns = ['service_id', 'price', 'duration_minutes', 'category_id'];

    public function getWithCategory(): array
    {
        $sql = "SELECT s.*, sc.category_name 
                FROM services s 
                JOIN service_categories sc ON s.category_id = sc.category_id
                ORDER BY s.price DESC";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function getByCategory(int $categoryId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM services WHERE category_id = ? ORDER BY service_name");
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll();
    }
}
