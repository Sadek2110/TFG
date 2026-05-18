<?php

use PHPUnit\Framework\TestCase;

class PartidoTest extends TestCase
{
    private Partido $partido;
    private Equipo $equipo;

    protected function setUp(): void
    {
        test_reset();
        $this->partido = new Partido();
        $this->equipo = new Equipo();
    }

    private function seedMatch(string $status = 'pending'): array
    {
        $t1 = test_create_team('Home FC', 'Madrid', 1);
        $t2 = test_create_team('Away FC', 'Madrid', 2);
        $f  = test_create_field('Campo Test', 'Madrid');
        $l  = test_create_league('Liga Test', 'Madrid');
        Database::run('INSERT INTO league_teams (league_id,team_id) VALUES (?,?)', [(int) $l['id'], (int) $t1['id']]);
        Database::run('INSERT INTO league_teams (league_id,team_id) VALUES (?,?)', [(int) $l['id'], (int) $t2['id']]);

        Database::run(
            "INSERT INTO matches (home_team_id,away_team_id,league_id,field_id,scheduled_at,status,created_by)
             VALUES (?,?,?,?,datetime('now','+2 days'),?,1)",
            [(int) $t1['id'], (int) $t2['id'], (int) $l['id'], (int) $f['id'], $status]
        );

        return [
            'match' => $this->partido->find(Database::insertId()),
            'home'  => $t1,
            'away'  => $t2,
        ];
    }

    // ===== create() =====
    public function test_create_match(): void
    {
        $t1 = test_create_team('Home FC', 'Madrid', 1);
        $t2 = test_create_team('Away FC', 'Madrid', 2);
        $f  = test_create_field('Campo Test', 'Madrid');
        $l  = test_create_league('Liga Test', 'Madrid');
        Database::run('INSERT INTO league_teams (league_id,team_id) VALUES (?,?)', [(int) $l['id'], (int) $t1['id']]);
        Database::run('INSERT INTO league_teams (league_id,team_id) VALUES (?,?)', [(int) $l['id'], (int) $t2['id']]);

        $futureDate = date('Y-m-d H:i:s', time() + 86400);
        [$match, $errors] = $this->partido->create(1, [
            'home_team_id' => (int) $t1['id'],
            'away_team_id' => (int) $t2['id'],
            'field_id'     => (int) $f['id'],
            'league_id'    => (int) $l['id'],
            'scheduled_at' => $futureDate,
        ]);

        $this->assertEmpty($errors);
        $this->assertNotNull($match);
        $this->assertSame('pending', $match['status']);
    }

    public function test_create_match_same_team(): void
    {
        $t = test_create_team('Solo FC', 'Madrid', 1);
        $futureDate = date('Y-m-d H:i:s', time() + 86400);
        [, $errors] = $this->partido->create(1, [
            'home_team_id' => (int) $t['id'],
            'away_team_id' => (int) $t['id'],
            'scheduled_at' => $futureDate,
        ]);

        $this->assertArrayHasKey('away_team_id', $errors);
    }

    public function test_create_match_past_date(): void
    {
        $t1 = test_create_team('H', 'Madrid', 1);
        $t2 = test_create_team('A', 'Madrid', 2);
        $pastDate = '2020-01-01 10:00:00';
        [, $errors] = $this->partido->create(1, [
            'home_team_id' => (int) $t1['id'],
            'away_team_id' => (int) $t2['id'],
            'scheduled_at' => $pastDate,
        ]);

        $this->assertArrayHasKey('scheduled_at', $errors);
    }

    public function test_create_match_invalid_date(): void
    {
        $t1 = test_create_team('H', 'Madrid', 1);
        $t2 = test_create_team('A', 'Madrid', 2);
        [, $errors] = $this->partido->create(1, [
            'home_team_id' => (int) $t1['id'],
            'away_team_id' => (int) $t2['id'],
            'scheduled_at' => 'not-a-date',
        ]);

        $this->assertArrayHasKey('scheduled_at', $errors);
    }

    public function test_create_match_nonexistent_field(): void
    {
        $t1 = test_create_team('H', 'Madrid', 1);
        $t2 = test_create_team('A', 'Madrid', 2);
        $futureDate = date('Y-m-d H:i:s', time() + 86400);
        [, $errors] = $this->partido->create(1, [
            'home_team_id' => (int) $t1['id'],
            'away_team_id' => (int) $t2['id'],
            'field_id'     => 9999,
            'scheduled_at' => $futureDate,
        ]);

        $this->assertArrayHasKey('field_id', $errors);
    }

    public function test_create_match_teams_not_in_league(): void
    {
        $t1 = test_create_team('H', 'Madrid', 1);
        $t2 = test_create_team('A', 'Madrid', 2);
        $l  = test_create_league('Liga', 'Madrid');
        $futureDate = date('Y-m-d H:i:s', time() + 86400);

        [, $errors] = $this->partido->create(1, [
            'home_team_id' => (int) $t1['id'],
            'away_team_id' => (int) $t2['id'],
            'league_id'    => (int) $l['id'],
            'scheduled_at' => $futureDate,
        ]);

        $this->assertArrayHasKey('league_id', $errors);
    }

    // ===== find() =====
    public function test_find_match(): void
    {
        $data = $this->seedMatch();
        $this->assertNotNull($data['match']);
        $this->assertSame('VS', $data['match']['s']); // pending -> 'VS'
    }

    // ===== card() (private, tested via find output) =====
    public function test_card_pending_shows_vs(): void
    {
        $data = $this->seedMatch('pending');
        $this->assertSame('VS', $data['match']['s']);
        $this->assertSame('Pendiente', $data['match']['lbl']);
        $this->assertArrayHasKey('d', $data['match']);
        $this->assertArrayHasKey('m', $data['match']);
        $this->assertArrayHasKey('t', $data['match']);
    }

    public function test_card_confirmed_shows_vs(): void
    {
        $data = $this->seedMatch('confirmed');
        $this->assertSame('VS', $data['match']['s']);
        $this->assertSame('Confirmado', $data['match']['lbl']);
    }

    public function test_card_finished_shows_score(): void
    {
        $data = $this->seedMatch('finished');
        // Need to set scores manually since seed creates finished but without scores
        Database::run('UPDATE matches SET home_score=3, away_score=1 WHERE id=?', [(int) $data['match']['id']]);
        $m = $this->partido->find((int) $data['match']['id']);
        $this->assertSame('3 – 1', $m['s']);
        $this->assertSame('Finalizado', $m['lbl']);
    }

    public function test_card_cancelled_shows_cxl(): void
    {
        $data = $this->seedMatch('cancelled');
        $this->assertSame('CXL', $data['match']['s']);
        $this->assertSame('Cancelado', $data['match']['lbl']);
    }

    // ===== setStatus() =====
    public function test_set_status_pending_to_confirmed(): void
    {
        $data = $this->seedMatch('pending');
        $result = $this->partido->setStatus((int) $data['match']['id'], 'confirmed');
        $this->assertTrue($result);

        $m = $this->partido->find((int) $data['match']['id']);
        $this->assertSame('confirmed', $m['status']);
    }

    public function test_set_status_confirmed_to_finished(): void
    {
        $data = $this->seedMatch('confirmed');
        $result = $this->partido->setStatus((int) $data['match']['id'], 'finished', 2, 1);
        $this->assertTrue($result);

        $m = $this->partido->find((int) $data['match']['id']);
        $this->assertSame('finished', $m['status']);
        $this->assertSame(2, (int) $m['home_score']);
        $this->assertSame(1, (int) $m['away_score']);
    }

    public function test_set_status_confirmed_to_cancelled(): void
    {
        $data = $this->seedMatch('confirmed');
        $result = $this->partido->setStatus((int) $data['match']['id'], 'cancelled');
        $this->assertTrue($result);

        $m = $this->partido->find((int) $data['match']['id']);
        $this->assertSame('cancelled', $m['status']);
    }

    public function test_set_status_pending_to_cancelled(): void
    {
        $data = $this->seedMatch('pending');
        $result = $this->partido->setStatus((int) $data['match']['id'], 'cancelled');
        $this->assertTrue($result);
    }

    public function test_set_status_pending_to_finished_fails(): void
    {
        $data = $this->seedMatch('pending');
        $result = $this->partido->setStatus((int) $data['match']['id'], 'finished', 1, 1);
        $this->assertFalse($result);
    }

    public function test_set_status_cannot_revert_to_pending(): void
    {
        $data = $this->seedMatch('confirmed');
        $result = $this->partido->setStatus((int) $data['match']['id'], 'pending');
        $this->assertFalse($result);
    }

    public function test_set_status_finished_is_final(): void
    {
        $data = $this->seedMatch('finished');
        $result = $this->partido->setStatus((int) $data['match']['id'], 'confirmed');
        $this->assertFalse($result);
    }

    public function test_set_status_cancelled_is_final(): void
    {
        $data = $this->seedMatch('cancelled');
        $result = $this->partido->setStatus((int) $data['match']['id'], 'confirmed');
        $this->assertFalse($result);
    }

    public function test_set_status_confirmed_to_confirmed_is_noop(): void
    {
        $data = $this->seedMatch('confirmed');
        // Confirmed→Confirmed is NOT allowed — already confirmed
        $result = $this->partido->setStatus((int) $data['match']['id'], 'confirmed');
        $this->assertFalse($result);
    }

    public function test_set_status_invalid_status(): void
    {
        $data = $this->seedMatch('pending');
        $result = $this->partido->setStatus((int) $data['match']['id'], 'invalid');
        $this->assertFalse($result);
    }

    public function test_set_status_nonexistent_match(): void
    {
        $result = $this->partido->setStatus(9999, 'confirmed');
        $this->assertFalse($result);
    }

    // ===== all() =====
    public function test_all_matches(): void
    {
        $this->seedMatch('pending');
        $this->seedMatch('confirmed');
        $all = $this->partido->all();
        $this->assertCount(2, $all);
    }

    // ===== upcoming() =====
    public function test_upcoming_returns_future_matches(): void
    {
        $data1 = $this->seedMatch('confirmed');
        $m1 = $data1['match'];

        $upcoming = $this->partido->upcoming(10);
        $this->assertNotEmpty($upcoming);
        $this->assertArrayHasKey('home', $upcoming[0]);
        $this->assertArrayHasKey('away', $upcoming[0]);
        $this->assertArrayHasKey('when', $upcoming[0]);
    }

    // ===== deleteIfAllowed() =====
    public function test_delete_if_allowed_as_admin(): void
    {
        $data = $this->seedMatch('pending');
        $result = $this->partido->deleteIfAllowed(
            (int) $data['match']['id'], 999, true, $this->equipo
        );
        $this->assertTrue($result);
        $this->assertNull($this->partido->find((int) $data['match']['id']));
    }

    public function test_delete_if_allowed_as_captain(): void
    {
        $data = $this->seedMatch('pending');
        // captain of home team (user 1 created the team via test_create_team)
        $result = $this->partido->deleteIfAllowed(
            (int) $data['match']['id'], 1, false, $this->equipo
        );
        $this->assertTrue($result);
    }

    public function test_delete_if_not_allowed(): void
    {
        $data = $this->seedMatch('pending');
        $result = $this->partido->deleteIfAllowed(
            (int) $data['match']['id'], 9999, false, $this->equipo
        );
        $this->assertFalse($result);
    }

    public function test_delete_if_allowed_nonexistent_match(): void
    {
        $result = $this->partido->deleteIfAllowed(9999, 1, true, $this->equipo);
        $this->assertFalse($result);
    }

    // ===== delete() =====
    public function test_delete_match(): void
    {
        $data = $this->seedMatch();
        $this->partido->delete((int) $data['match']['id']);
        $this->assertNull($this->partido->find((int) $data['match']['id']));
    }
}
