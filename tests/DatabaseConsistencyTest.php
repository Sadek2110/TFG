<?php

use PHPUnit\Framework\TestCase;

final class DatabaseConsistencyTest extends TestCase
{
    public function testSeededDemoCredentialsMatchDocumentation(): void
    {
        $usuario = new Usuario();

        $admin = $usuario->login('admin@fastplay.es', 'Admin1234!', '127.0.0.1');
        $demo = $usuario->login('demo@fastplay.es', 'Demo1234!', '127.0.0.1');

        self::assertSame('admin', $admin['role'] ?? null);
        self::assertSame('player', $demo['role'] ?? null);
    }

    public function testRepairConsistencyRegistersTeamsUsedByLeagueMatches(): void
    {
        test_reset();

        $admin = test_create_user('Admin', 'admin@test.com', 'password123', 'admin');
        $homeCaptain = test_create_user('Home Captain', 'home@test.com');
        $awayCaptain = test_create_user('Away Captain', 'away@test.com');
        $home = test_create_team('Home FC', 'Madrid', (int) $homeCaptain['id']);
        $away = test_create_team('Away FC', 'Madrid', (int) $awayCaptain['id']);
        $league = test_create_league();
        $field = test_create_field();

        Database::run('INSERT INTO league_teams (league_id, team_id) VALUES (?, ?)', [$league['id'], $home['id']]);
        Database::run(
            'INSERT INTO matches (home_team_id, away_team_id, league_id, field_id, scheduled_at, status, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            [$home['id'], $away['id'], $league['id'], $field['id'], '2026-06-12 19:30:00', 'pending', $admin['id']]
        );

        $repair = new ReflectionMethod(Database::class, 'repairConsistency');
        $repair->setAccessible(true);
        $repair->invoke(null, Database::pdo());

        $registered = (int) Database::value(
            'SELECT COUNT(*) FROM league_teams WHERE league_id = ? AND team_id IN (?, ?)',
            [$league['id'], $home['id'], $away['id']]
        );

        self::assertSame(2, $registered);
    }
}
