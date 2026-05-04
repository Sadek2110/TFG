<?php
declare(strict_types=1);

abstract class Model
{
    protected PDO    $db;
    protected string $table  = '';
    protected string $pk     = 'id';
    protected array  $allowedSortColumns = ['id', 'created_at', 'name', 'match_date', 'start_date'];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function validateOrderBy(string $orderBy): string
    {
        $parts    = explode(' ', trim($orderBy));
        $column   = $parts[0];
        $direction = strtoupper($parts[1] ?? 'DESC');

        if (!in_array($column, $this->allowedSortColumns)) {
            $column = $this->pk;
        }
        if (!in_array($direction, ['ASC', 'DESC'])) {
            $direction = 'DESC';
        }
        return "{$column} {$direction}";
    }

    public function findById(int $id): array|false
    {
        $st = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->pk} = ?");
        $st->execute([$id]);
        return $st->fetch();
    }

    public function findAll(string $orderBy = 'id DESC', int $limit = 100): array
    {
        $orderBy = $this->validateOrderBy($orderBy);
        $st = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY {$orderBy} LIMIT ?");
        $st->execute([$limit]);
        return $st->fetchAll();
    }

    public function findWhere(array $conditions, string $orderBy = 'id DESC', int $limit = 100): array
    {
        $orderBy = $this->validateOrderBy($orderBy);
        [$where, $vals] = $this->buildWhere($conditions);
        $st = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$where} ORDER BY {$orderBy} LIMIT ?");
        $st->execute([...$vals, $limit]);
        return $st->fetchAll();
    }

    public function findOneWhere(array $conditions): array|false
    {
        [$where, $vals] = $this->buildWhere($conditions);
        $st = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$where} LIMIT 1");
        $st->execute($vals);
        return $st->fetch();
    }

    public function insert(array $data): int
    {
        $cols = implode(', ', array_keys($data));
        $plh  = implode(', ', array_fill(0, count($data), '?'));
        $st   = $this->db->prepare("INSERT INTO {$this->table} ({$cols}) VALUES ({$plh})");
        $st->execute(array_values($data));
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $set = implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($data)));
        $st  = $this->db->prepare("UPDATE {$this->table} SET {$set} WHERE {$this->pk} = ?");
        return $st->execute([...array_values($data), $id]);
    }

    public function delete(int $id): bool
    {
        $st = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->pk} = ?");
        return $st->execute([$id]);
    }

    public function count(array $conditions = []): int
    {
        if (empty($conditions)) {
            return (int) $this->db->query("SELECT COUNT(*) FROM {$this->table}")->fetchColumn();
        }
        [$where, $vals] = $this->buildWhere($conditions);
        $st = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE {$where}");
        $st->execute($vals);
        return (int) $st->fetchColumn();
    }

    public function paginate(int $page = 1, int $perPage = 20, string $orderBy = 'id DESC', array $conditions = []): array
    {
        $orderBy = $this->validateOrderBy($orderBy);
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        [$where, $vals] = empty($conditions) ? ['', []] : $this->buildWhere($conditions);
        $whereClause = empty($where) ? '' : "WHERE {$where}";

        $st = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} {$whereClause}");
        $st->execute($vals);
        $total = (int) $st->fetchColumn();

        $st = $this->db->prepare(
            "SELECT * FROM {$this->table} {$whereClause} ORDER BY {$orderBy} LIMIT ? OFFSET ?"
        );
        $st->execute([...$vals, $perPage, $offset]);

        return [
            'data'        => $st->fetchAll(),
            'current'     => $page,
            'per_page'    => $perPage,
            'total'       => $total,
            'total_pages' => (int) ceil($total / $perPage),
        ];
    }

    private function buildWhere(array $conditions): array
    {
        $clauses = [];
        $values  = [];
        foreach ($conditions as $col => $val) {
            if ($val === null) {
                $clauses[] = "{$col} IS NULL";
            } else {
                $clauses[] = "{$col} = ?";
                $values[]  = $val;
            }
        }
        return [implode(' AND ', $clauses), $values];
    }
}
