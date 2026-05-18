<?php
// FastPlay · modelo Partido

class Partido
{
    public function all(): array
    {
        $rows = Database::all(
            "SELECT m.*, h.name AS home_name, a.name AS away_name, f.name AS field_name, l.name AS league_name
             FROM matches m
             JOIN teams h ON h.id = m.home_team_id
             JOIN teams a ON a.id = m.away_team_id
             LEFT JOIN fields f ON f.id = m.field_id
             LEFT JOIN leagues l ON l.id = m.league_id
             ORDER BY m.scheduled_at ASC"
        );
        return array_map([$this, 'card'], $rows);
    }

    public function find(int $id): ?array
    {
        $row = Database::one(
            "SELECT m.*, h.name AS home_name, a.name AS away_name, f.name AS field_name, l.name AS league_name
             FROM matches m
             JOIN teams h ON h.id = m.home_team_id
             JOIN teams a ON a.id = m.away_team_id
             LEFT JOIN fields f ON f.id = m.field_id
             LEFT JOIN leagues l ON l.id = m.league_id
             WHERE m.id = ?", [$id]
        );
        return $row ? $this->card($row) : null;
    }

    public function upcoming(int $limit = 5): array
    {
        $rows = Database::all(
            "SELECT m.*, h.name AS home_name, a.name AS away_name
             FROM matches m
             JOIN teams h ON h.id = m.home_team_id
             JOIN teams a ON a.id = m.away_team_id
             WHERE m.status IN ('confirmed','pending') AND m.scheduled_at >= ?
             ORDER BY m.scheduled_at ASC
             LIMIT ?", [date('Y-m-d H:i:s'), $limit]
        );
        return array_map(function ($r) {
            return [
                'home' => $r['home_name'],
                'away' => $r['away_name'],
                'when' => date('d/m H:i', strtotime($r['scheduled_at'])),
                'id'   => (int) $r['id'],
            ];
        }, $rows);
    }

    public function create(int $userId, array $data): array
    {
        $errors = [];
        $home = (int) ($data['home_team_id'] ?? 0);
        $away = (int) ($data['away_team_id'] ?? 0);
        $when = trim((string) ($data['scheduled_at'] ?? ''));
        $field = (int) ($data['field_id'] ?? 0);
        $league = (int) ($data['league_id'] ?? 0);

        if ($home <= 0)                           $errors['home_team_id'] = 'Selecciona equipo local.';
        if ($away <= 0)                           $errors['away_team_id'] = 'Selecciona equipo visitante.';
        if ($home && $away && $home === $away)    $errors['away_team_id'] = 'Los equipos deben ser distintos.';
        $ts = strtotime($when);
        if (!$ts)                                  $errors['scheduled_at'] = 'Fecha/hora inválida.';
        elseif ($ts < time() - 60)                 $errors['scheduled_at'] = 'La fecha debe ser futura.';

        if ($field > 0 && !Database::value('SELECT 1 FROM fields WHERE id=?', [$field])) {
            $errors['field_id'] = 'El campo seleccionado no existe.';
        }
        if ($league > 0 && !Database::value('SELECT 1 FROM leagues WHERE id=?', [$league])) {
            $errors['league_id'] = 'La liga seleccionada no existe.';
        }

        if (!$errors && $league > 0) {
            $homeIn = (int) Database::value('SELECT 1 FROM league_teams WHERE league_id=? AND team_id=?', [$league, $home]);
            $awayIn = (int) Database::value('SELECT 1 FROM league_teams WHERE league_id=? AND team_id=?', [$league, $away]);
            if (!$homeIn || !$awayIn) {
                $errors['league_id'] = 'Ambos equipos deben estar inscritos en la liga seleccionada.';
            }
        }
        if ($errors) return [null, $errors];

        Database::run(
            "INSERT INTO matches (home_team_id,away_team_id,league_id,field_id,scheduled_at,status,created_by)
             VALUES (?,?,?,?,?, 'pending', ?)",
            [$home, $away, $league ?: null, $field ?: null, date('Y-m-d H:i:s', $ts), $userId]
        );
        return [$this->find(Database::insertId()), []];
    }

    public function setStatus(int $id, string $status, ?int $homeScore = null, ?int $awayScore = null): bool
    {
        if (!in_array($status, ['pending','confirmed','cancelled','finished'], true)) {
            return false;
        }
        $current = Database::one('SELECT status FROM matches WHERE id=?', [$id]);
        if (!$current) {
            return false;
        }
        $from = (string) $current['status'];
        if (in_array($from, ['finished','cancelled'], true)) {
            return false;
        }
        if ($status === 'pending') {
            return false;
        }
        if ($status === 'confirmed' && $from !== 'pending') {
            return false;
        }
        if ($status === 'finished' && $from !== 'confirmed') {
            return false;
        }
        if ($status === 'finished') {
            Database::run('UPDATE matches SET status=?, home_score=?, away_score=? WHERE id=?', [$status, $homeScore, $awayScore, $id]);
        } else {
            Database::run('UPDATE matches SET status=? WHERE id=?', [$status, $id]);
        }
        return true;
    }

    public function delete(int $id): void
    {
        Database::run('DELETE FROM matches WHERE id=?', [$id]);
    }

    public function deleteIfAllowed(int $matchId, int $userId, bool $isAdmin, Equipo $equipo): bool
    {
        $m = $this->find($matchId);
        if (!$m) return false;
        if (!$isAdmin
            && !$equipo->isCaptain((int) $m['home_team_id'], $userId)
            && !$equipo->isCaptain((int) $m['away_team_id'], $userId)) {
            return false;
        }
        $this->delete($matchId);
        return true;
    }

    private function card(array $r): array
    {
        $ts = strtotime($r['scheduled_at']);
        $score = '–';
        if ($r['status'] === 'finished') {
            $score = ((int) $r['home_score']) . ' – ' . ((int) $r['away_score']);
        } elseif ($r['status'] === 'cancelled') {
            $score = 'CXL';
        } else {
            $score = 'VS';
        }
        $months = ['ENE','FEB','MAR','ABR','MAY','JUN','JUL','AGO','SEP','OCT','NOV','DIC'];
        $labels = [
            'pending'   => 'Pendiente',
            'confirmed' => 'Confirmado',
            'cancelled' => 'Cancelado',
            'finished'  => 'Finalizado',
        ];
        return array_merge($r, [
            'd'    => $ts ? date('d', $ts) : '–',
            'm'    => $ts ? $months[(int) date('n', $ts) - 1] : '',
            't'    => $ts ? date('H:i', $ts) : '',
            'h'    => $r['home_name'],
            'a'    => $r['away_name'],
            's'    => $score,
            'st'   => $r['status'],
            'lbl'  => $labels[$r['status']] ?? $r['status'],
            'f'    => $r['field_name'] ?? 'Campo a confirmar',
        ]);
    }
}
