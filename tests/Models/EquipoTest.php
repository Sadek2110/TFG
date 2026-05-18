<?php

use PHPUnit\Framework\TestCase;

class EquipoTest extends TestCase
{
    private Equipo $equipo;

    protected function setUp(): void
    {
        test_reset();
        $this->equipo = new Equipo();
    }

    // ===== create() =====
    public function test_create_team(): void
    {
        $captain = test_create_user('Capitán', 'cap@test.com');
        [$team, $errors] = $this->equipo->create((int) $captain['id'], [
            'name' => 'Real Test FC', 'city' => 'Madrid', 'badge' => '⚽',
        ]);

        $this->assertEmpty($errors);
        $this->assertNotNull($team);
        $this->assertSame('Real Test FC', $team['name']);
        $this->assertSame('⚽', $team['badge']);
    }

    public function test_create_team_short_name(): void
    {
        $captain = test_create_user();
        [$team, $errors] = $this->equipo->create((int) $captain['id'], [
            'name' => 'AB', 'city' => 'Madrid',
        ]);

        $this->assertNull($team);
        $this->assertArrayHasKey('name', $errors);
    }

    public function test_create_team_missing_city(): void
    {
        $captain = test_create_user();
        [$team, $errors] = $this->equipo->create((int) $captain['id'], [
            'name' => 'Test FC', 'city' => '',
        ]);

        $this->assertNull($team);
        $this->assertArrayHasKey('city', $errors);
    }

    public function test_create_team_long_badge(): void
    {
        $captain = test_create_user();
        [$team, $errors] = $this->equipo->create((int) $captain['id'], [
            'name' => 'Test FC', 'city' => 'Madrid', 'badge' => 'too long',
        ]);

        $this->assertNull($team);
        $this->assertArrayHasKey('badge', $errors);
    }

    public function test_create_team_duplicate(): void
    {
        $captain = test_create_user();
        $this->equipo->create((int) $captain['id'], ['name' => 'Dup FC', 'city' => 'Madrid']);
        [$team, $errors] = $this->equipo->create((int) $captain['id'], ['name' => 'Dup FC', 'city' => 'Madrid']);

        $this->assertNull($team);
        $this->assertArrayHasKey('name', $errors);
    }

    public function test_create_team_auto_joins_captain(): void
    {
        $captain = test_create_user('Cap', 'cap2@test.com');
        [$team] = $this->equipo->create((int) $captain['id'], [
            'name' => 'Auto Join FC', 'city' => 'Valencia',
        ]);

        $members = $this->equipo->members((int) $team['id']);
        $this->assertCount(1, $members);
        $this->assertSame('Cap', $members[0]['name']);
        $this->assertSame(1, (int) $members[0]['is_captain']);
    }

    // ===== find() =====
    public function test_find_team(): void
    {
        $t = test_create_team('Find FC', 'Bilbao', 1);
        $found = $this->equipo->find((int) $t['id']);
        $this->assertNotNull($found);
        $this->assertSame('Find FC', $found['name']);
    }

    public function test_find_nonexistent(): void
    {
        $this->assertNull($this->equipo->find(9999));
    }

    // ===== all() =====
    public function test_all_teams(): void
    {
        test_create_team('A FC', 'Madrid', 1);
        test_create_team('B FC', 'Barcelona', 2);
        $all = $this->equipo->all();
        $this->assertCount(2, $all);
    }

    // ===== join() =====
    public function test_join_team(): void
    {
        $captain = test_create_user('Cap', 'cap3@test.com');
        $player = test_create_user('Player', 'player@test.com');
        [$team] = $this->equipo->create((int) $captain['id'], ['name' => 'Join FC', 'city' => 'Madrid']);

        $result = $this->equipo->join((int) $team['id'], (int) $player['id']);
        $this->assertTrue($result);

        $members = $this->equipo->members((int) $team['id']);
        $this->assertCount(2, $members);
    }

    public function test_join_duplicate(): void
    {
        $captain = test_create_user('Cap', 'cap4@test.com');
        [$team] = $this->equipo->create((int) $captain['id'], ['name' => 'DupJoin FC', 'city' => 'Madrid']);

        $this->assertFalse($this->equipo->join((int) $team['id'], (int) $captain['id']));
    }

    // ===== leave() =====
    public function test_leave_team(): void
    {
        $captain = test_create_user('Cap', 'cap5@test.com');
        $player = test_create_user('Player', 'player2@test.com');
        [$team] = $this->equipo->create((int) $captain['id'], ['name' => 'Leave FC', 'city' => 'Madrid']);
        $this->equipo->join((int) $team['id'], (int) $player['id']);

        $result = $this->equipo->leave((int) $team['id'], (int) $player['id']);
        $this->assertTrue($result);

        $members = $this->equipo->members((int) $team['id']);
        $this->assertCount(1, $members);
    }

    public function test_leave_as_captain_fails(): void
    {
        $captain = test_create_user('Cap', 'cap6@test.com');
        [$team] = $this->equipo->create((int) $captain['id'], ['name' => 'CaptLeave FC', 'city' => 'Madrid']);

        $result = $this->equipo->leave((int) $team['id'], (int) $captain['id']);
        $this->assertFalse($result);
    }

    // ===== ofUser() =====
    public function test_of_user(): void
    {
        $captain = test_create_user('Cap', 'cap7@test.com');
        [$team] = $this->equipo->create((int) $captain['id'], ['name' => 'UserTeam FC', 'city' => 'Madrid']);
        $teams = $this->equipo->ofUser((int) $captain['id']);
        $this->assertCount(1, $teams);
        $this->assertSame('UserTeam FC', $teams[0]['name']);
    }

    // ===== mine() =====
    public function test_mine_returns_team(): void
    {
        $captain = test_create_user('Cap', 'cap8@test.com');
        [$team] = $this->equipo->create((int) $captain['id'], ['name' => 'Mine FC', 'city' => 'Madrid']);
        $mine = $this->equipo->mine((int) $captain['id']);
        $this->assertNotNull($mine);
        $this->assertSame('Mine FC', $mine['name']);
    }

    public function test_mine_returns_null_for_user_without_teams(): void
    {
        $u = test_create_user('No Team', 'noteam@test.com');
        $this->assertNull($this->equipo->mine((int) $u['id']));
    }

    // ===== isCaptain() =====
    public function test_is_captain(): void
    {
        $captain = test_create_user('Cap', 'cap9@test.com');
        [$team] = $this->equipo->create((int) $captain['id'], ['name' => 'CapTest FC', 'city' => 'Madrid']);
        $this->assertTrue($this->equipo->isCaptain((int) $team['id'], (int) $captain['id']));
        $this->assertFalse($this->equipo->isCaptain((int) $team['id'], 9999));
    }

    // ===== deletionBlocker() =====
    public function test_deletion_blocker_no_matches(): void
    {
        $t = test_create_team('NoMatch FC', 'Madrid', 1);
        $this->assertNull($this->equipo->deletionBlocker((int) $t['id']));
    }

    public function test_deletion_blocker_with_matches(): void
    {
        $t1 = test_create_team('T1', 'Madrid', 1);
        $t2 = test_create_team('T2', 'Madrid', 2);
        Database::run(
            "INSERT INTO matches (home_team_id,away_team_id,scheduled_at,created_by) VALUES (?,?,datetime('now','+1 day'),?)",
            [(int) $t1['id'], (int) $t2['id'], 1]
        );

        $blocker = $this->equipo->deletionBlocker((int) $t1['id']);
        $this->assertNotNull($blocker);
        $this->assertStringContainsString('partidos asociados', $blocker);
    }

    // ===== delete() =====
    public function test_delete_team(): void
    {
        $t = test_create_team('Delete FC', 'Madrid', 1);
        $this->equipo->delete((int) $t['id']);
        $this->assertNull($this->equipo->find((int) $t['id']));
    }
}
