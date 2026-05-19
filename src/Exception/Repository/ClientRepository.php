<?php
declare(strict_types=1);

class ClientRepository extends AbstractRepository
{
    protected string $table = 'clients';
    protected string $primaryKey = 'client_id';
    protected array $allowedSortColumns = ['client_id', 'last_name', 'first_name', 'created_at'];

    public function findByPhone(string $phone): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE phone = ?");
        $stmt->execute([$phone]);
        return $stmt->fetch() ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }
}
