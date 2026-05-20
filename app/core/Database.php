<?php
// FastPlay · wrapper PDO (MySQL / PostgreSQL)

class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }
        self::$pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        return self::$pdo;
    }

    public static function run(string $sql, array $params = []): PDOStatement
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($params);
        return $st;
    }

    public static function one(string $sql, array $params = []): ?array
    {
        $row = self::run($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    public static function all(string $sql, array $params = []): array
    {
        return self::run($sql, $params)->fetchAll();
    }

    public static function value(string $sql, array $params = [])
    {
        $st  = self::run($sql, $params);
        $row = $st->fetch(PDO::FETCH_NUM);
        return $row === false ? null : $row[0];
    }

    public static function insertId(): int
    {
        return (int) self::pdo()->lastInsertId();
    }

    private static function repairConsistency(PDO $pdo): void
    {
        $matches = $pdo->query('SELECT home_team_id, away_team_id, league_id FROM matches WHERE league_id IS NOT NULL')->fetchAll(PDO::FETCH_ASSOC);
        foreach ($matches as $m) {
            $leagueId = $m['league_id'];
            foreach ([$m['home_team_id'], $m['away_team_id']] as $teamId) {
                if ($teamId !== null) {
                    $st = $pdo->prepare('SELECT COUNT(*) FROM league_teams WHERE league_id = ? AND team_id = ?');
                    $st->execute([$leagueId, $teamId]);
                    if ((int) $st->fetchColumn() === 0) {
                        $ins = $pdo->prepare('INSERT INTO league_teams (league_id, team_id) VALUES (?, ?)');
                        $ins->execute([$leagueId, $teamId]);
                    }
                }
            }
        }
    }
}
