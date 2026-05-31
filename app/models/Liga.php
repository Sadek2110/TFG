<?php
// FastPlay · modelo Liga

class Liga
{
    public function all(): array
    {
        $rows = Database::all(
            "SELECT l.*, (SELECT COUNT(*) FROM league_teams WHERE league_id = l.id) AS team_count
             FROM leagues l ORDER BY pro DESC, start_date ASC"
        );
        return array_map([$this, 'normalize'], $rows);
    }

    public function find(int $id): ?array
    {
        $row = Database::one('SELECT * FROM leagues WHERE id = ?', [$id]);
        return $row ? $this->normalize($row) : null;
    }

    public function standings(int $leagueId): array
    {
        return Database::all(
            "SELECT lt.*, t.name AS team_name, t.city AS team_city, t.badge
             FROM league_teams lt
             JOIN teams t ON t.id = lt.team_id
             WHERE lt.league_id = ?
             ORDER BY lt.points DESC, (lt.gf - lt.ga) DESC, lt.gf DESC, t.name ASC",
            [$leagueId]
        );
    }

    public function isTeamRegistered(int $leagueId, int $teamId): bool
    {
        return (bool) Database::value('SELECT 1 FROM league_teams WHERE league_id=? AND team_id=?', [$leagueId, $teamId]);
    }

    public function register(int $leagueId, int $teamId): array
    {
        $league = $this->find($leagueId);
        if (!$league) return ['error' => 'Liga no encontrada.'];
        if ($league['status'] !== 'open') return ['error' => 'Esta liga no admite inscripciones.'];

        $count = (int) Database::value('SELECT COUNT(*) FROM league_teams WHERE league_id=?', [$leagueId]);
        if ($count >= (int) $league['max_teams']) {
            return ['error' => 'La liga ya está completa.'];
        }
        if ($this->isTeamRegistered($leagueId, $teamId)) {
            return ['error' => 'El equipo ya está inscrito.'];
        }
        Database::run('INSERT INTO league_teams (league_id,team_id) VALUES (?,?)', [$leagueId, $teamId]);
        return ['ok' => true];
    }

    public function create(array $data): array
    {
        $errors = [];
        $name  = trim((string) ($data['name'] ?? ''));
        $city  = trim((string) ($data['city'] ?? ''));
        $pro   = !empty($data['pro']) ? 1 : 0;
        $prize = $pro ? (float) ($data['prize'] ?? 0) : null;
        $start = trim((string) ($data['start_date'] ?? ''));
        $end   = trim((string) ($data['end_date'] ?? ''));
        $max   = max(2, (int) ($data['max_teams'] ?? 12));

        if (!v_required($name)) $errors['name'] = 'Nombre obligatorio.';
        if (!v_required($city)) $errors['city'] = 'Ciudad obligatoria.';
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start)) $errors['start_date'] = 'Fecha inicio inválida (YYYY-MM-DD).';
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $end))   $errors['end_date']   = 'Fecha fin inválida (YYYY-MM-DD).';
        if (!$errors && strtotime($end) <= strtotime($start)) $errors['end_date'] = 'La fecha fin debe ser posterior al inicio.';
        if ($errors) return [null, $errors];

        Database::run(
            "INSERT INTO leagues (name,city,pro,prize,start_date,end_date,max_teams) VALUES (?,?,?,?,?,?,?)",
            [$name, $city, $pro, $prize, $start, $end, $max]
        );
        return [$this->find(Database::insertId()), []];
    }

    public function delete(int $id): void
    {
        Database::run('DELETE FROM leagues WHERE id=?', [$id]);
    }

    public function stats(): array
    {
        $players = (int) Database::value('SELECT COUNT(*) FROM users');
        $matches = (int) Database::value('SELECT COUNT(*) FROM matches');
        $fields  = (int) Database::value('SELECT COUNT(*) FROM fields');
        return [
            ['v' => self::compact($players),  'l' => 'Jugadores'],
            ['v' => self::compact($matches),  'l' => 'Partidos'],
            ['v' => (string) ($fields ?: 12), 'l' => 'Campos'],
            ['v' => '100%',                   'l' => 'Gratis*', 'green' => true],
        ];
    }

    private function normalize(array $row): array
    {
        $row['pro']   = (bool) ($row['pro'] ?? false);
        $row['name']  = $row['name'] ?? '';
        $row['city']  = $row['city'] ?? '';
        $row['prize'] = isset($row['prize']) ? ($row['prize'] === null ? null : (float) $row['prize']) : null;
        $row['start'] = self::esDate($row['start_date'] ?? '');
        $row['end']   = self::esDate($row['end_date'] ?? '');
        return $row;
    }

    private static function esDate(string $iso): string
    {
        $t = strtotime($iso);
        return $t ? date('d/m/Y', $t) : $iso;
    }

    private static function compact(int $n): string
    {
        if ($n >= 1000) return number_format($n / 1000, 1, ',', '.') . 'K';
        return (string) $n;
    }
}