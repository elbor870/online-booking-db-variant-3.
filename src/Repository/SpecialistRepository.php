<?php
declare(strict_types=1);

class SpecialistRepository extends AbstractRepository
{
    protected string $table = 'specialists';
    protected string $primaryKey = 'specialist_id';
    protected array $allowedSortColumns = ['specialist_id', 'name', 'specialty'];

    public function findAllActive(): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} ORDER BY name ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}