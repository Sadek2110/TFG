<?php

use PHPUnit\Framework\TestCase;

class LigaTest extends TestCase
{
    private Liga $liga;

    protected function setUp(): void
    {
        test_reset();
        $this->liga = new Liga();
    }

    // ===== esDate() (private static, tested via normalize output) =====
    public function test_es_date_format(): void
    {
        // esDate cambia 'YYYY-MM-DD' a 'DD/MM/YYYY'
        $liga = $this->liga->find($this->createTestLeague()['id']);
        $this->assertSame('01/06/2026', $liga['start']);
        $this->assertSame('31/12/2026', $liga['end']);
    }

    public function test_es_date_invalid_returns_original(): void
    {
        // For invalid date, esDate returns the original string
        $ref = new ReflectionMethod(Liga::class, 'esDate');
        $ref->setAccessible(true);
        $this->assertSame('not-a-date', $ref->invoke(null, 'not-a-date'));
    }

    public function test_es_date_empty_string(): void
    {
        $ref = new ReflectionMethod(Liga::class, 'esDate');
        $ref->setAccessible(true);
        $this->assertSame('', $ref->invoke(null, ''));
    }

    // ===== compact() (private static) =====
    public function test_compact_below_1000(): void
    {
        $ref = new ReflectionMethod(Liga::class, 'compact');
        $ref->setAccessible(true);
        $this->assertSame('0', $ref->invoke(null, 0));
        $this->assertSame('1', $ref->invoke(null, 1));
        $this->assertSame('999', $ref->invoke(null, 999));
    }

    public function test_compact_at_1000(): void
    {
        $ref = new ReflectionMethod(Liga::class, 'compact');
        $ref->setAccessible(true);
        $this->assertSame('1,0K', $ref->invoke(null, 1000));
    }

    public function test_compact_large_numbers(): void
    {
        $ref = new ReflectionMethod(Liga::class, 'compact');
        $ref->setAccessible(true);
        $this->assertSame('5,0K', $ref->invoke(null, 5000));
        $this->assertSame('1,5K', $ref->invoke(null, 1500));
        $this->assertSame('99,9K', $ref->invoke(null, 99900));
    }

    // ===== normalize() (private) =====
    public function test_normalize_casts_pro_to_bool(): void
    {
        $ref = new ReflectionMethod(Liga::class, 'normalize');
        $ref->setAccessible(true);
        $result = $ref->invoke($this->liga, ['pro' => 1, 'start_date' => '2026-01-01', 'end_date' => '2026-06-30']);
        $this->assertTrue($result['pro']);
        $this->assertSame('01/01/2026', $result['start']);
        $this->assertSame('30/06/2026', $result['end']);
    }

    public function test_normalize_null_prize(): void
    {
        $ref = new ReflectionMethod(Liga::class, 'normalize');
        $ref->setAccessible(true);
        $result = $ref->invoke($this->liga, ['pro' => 0, 'prize' => null, 'start_date' => '2026-01-01', 'end_date' => '2026-06-30']);
        $this->assertNull($result['prize']);
    }

    public function test_normalize_float_prize(): void
    {
        $ref = new ReflectionMethod(Liga::class, 'normalize');
        $ref->setAccessible(true);
        $result = $ref->invoke($this->liga, ['pro' => 1, 'prize' => '1500.00', 'start_date' => '2026-01-01', 'end_date' => '2026-06-30']);
        $this->assertSame(1500.00, $result['prize']);
    }

    // ===== create() =====
    public function test_create_league(): void
    {
        [$liga, $errors] = $this->liga->create([
            'name' => 'Liga Test',
            'city' => 'Madrid',
            'start_date' => '2026-06-01',
            'end_date' => '2026-12-31',
        ]);

        $this->assertEmpty($errors);
        $this->assertNotNull($liga);
        $this->assertSame('Liga Test', $liga['name']);
        $this->assertFalse($liga['pro']);
    }

    public function test_create_pro_league(): void
    {
        [$liga, $errors] = $this->liga->create([
            'name' => 'Liga Pro Test',
            'city' => 'Barcelona',
            'pro' => 1,
            'prize' => '2000',
            'start_date' => '2026-06-01',
            'end_date' => '2026-12-31',
        ]);

        $this->assertEmpty($errors);
        $this->assertTrue($liga['pro']);
        $this->assertSame(2000.0, $liga['prize']);
    }

    public function test_create_league_missing_name(): void
    {
        [, $errors] = $this->liga->create([
            'name' => '', 'city' => 'Madrid',
            'start_date' => '2026-06-01', 'end_date' => '2026-12-31',
        ]);
        $this->assertArrayHasKey('name', $errors);
    }

    public function test_create_league_invalid_start_date(): void
    {
        [, $errors] = $this->liga->create([
            'name' => 'Test', 'city' => 'Madrid',
            'start_date' => '2026', 'end_date' => '2026-12-31',
        ]);
        $this->assertArrayHasKey('start_date', $errors);
    }

    public function test_create_league_invalid_end_date(): void
    {
        [, $errors] = $this->liga->create([
            'name' => 'Test', 'city' => 'Madrid',
            'start_date' => '2026-06-01', 'end_date' => '06/2026',
        ]);
        $this->assertArrayHasKey('end_date', $errors);
    }

    public function test_create_league_end_before_start(): void
    {
        [, $errors] = $this->liga->create([
            'name' => 'Test', 'city' => 'Madrid',
            'start_date' => '2026-06-01', 'end_date' => '2026-01-01',
        ]);
        $this->assertArrayHasKey('end_date', $errors);
    }

    // ===== find() =====
    public function test_find_league(): void
    {
        $l = $this->createTestLeague();
        $found = $this->liga->find((int) $l['id']);
        $this->assertNotNull($found);
        $this->assertSame('Test Liga', $found['name']);
    }

    public function test_find_nonexistent_league(): void
    {
        $this->assertNull($this->liga->find(9999));
    }

    // ===== all() =====
    public function test_all_sorted_by_pro(): void
    {
        $this->liga->create(['name' => 'Amateur', 'city' => 'Madrid', 'start_date' => '2026-06-01', 'end_date' => '2026-12-31']);
        $this->liga->create(['name' => 'Pro Liga', 'city' => 'Barcelona', 'pro' => 1, 'prize' => '1000', 'start_date' => '2026-06-01', 'end_date' => '2026-12-31']);

        $all = $this->liga->all();
        $this->assertCount(2, $all);
        $this->assertTrue($all[0]['pro']);
        $this->assertFalse($all[1]['pro']);
    }

    // ===== register() =====
    public function test_register_team_in_league(): void
    {
        $l = $this->createTestLeague();
        $t = test_create_team('Reg Team', 'Madrid', 1);

        $result = $this->liga->register((int) $l['id'], (int) $t['id']);
        $this->assertArrayHasKey('ok', $result);
        $this->assertTrue($this->liga->isTeamRegistered((int) $l['id'], (int) $t['id']));
    }

    public function test_register_duplicate(): void
    {
        $l = $this->createTestLeague();
        $t = test_create_team('Dup Team', 'Madrid', 1);

        $this->liga->register((int) $l['id'], (int) $t['id']);
        $result = $this->liga->register((int) $l['id'], (int) $t['id']);
        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('ya está inscrito', $result['error']);
    }

    public function test_register_nonexistent_league(): void
    {
        $t = test_create_team('Ghost Team', 'Madrid', 1);
        $result = $this->liga->register(9999, (int) $t['id']);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame('Liga no encontrada.', $result['error']);
    }

    // ===== isTeamRegistered() =====
    public function test_is_team_registered(): void
    {
        $l = $this->createTestLeague();
        $t = test_create_team('RegCheck', 'Madrid', 1);
        $this->assertFalse($this->liga->isTeamRegistered((int) $l['id'], (int) $t['id']));
        $this->liga->register((int) $l['id'], (int) $t['id']);
        $this->assertTrue($this->liga->isTeamRegistered((int) $l['id'], (int) $t['id']));
    }

    // ===== standings() =====
    public function test_standings_empty(): void
    {
        $l = $this->createTestLeague();
        $st = $this->liga->standings((int) $l['id']);
        $this->assertIsArray($st);
        $this->assertEmpty($st);
    }

    public function test_standings_ordered_by_points(): void
    {
        $l = $this->createTestLeague();
        $t1 = test_create_team('Top Team', 'Madrid', 1);
        $t2 = test_create_team('Bottom Team', 'Madrid', 2);

        $this->liga->register((int) $l['id'], (int) $t1['id']);
        $this->liga->register((int) $l['id'], (int) $t2['id']);

        Database::run('UPDATE league_teams SET points=? WHERE league_id=? AND team_id=?', [12, (int) $l['id'], (int) $t1['id']]);
        Database::run('UPDATE league_teams SET points=? WHERE league_id=? AND team_id=?', [3, (int) $l['id'], (int) $t2['id']]);

        $st = $this->liga->standings((int) $l['id']);
        $this->assertCount(2, $st);
        $this->assertSame(12, (int) $st[0]['points']);
        $this->assertSame(3, (int) $st[1]['points']);
    }

    // ===== delete() =====
    public function test_delete_league(): void
    {
        $l = $this->createTestLeague();
        $this->liga->delete((int) $l['id']);
        $this->assertNull($this->liga->find((int) $l['id']));
    }

    // ===== stats() =====
    public function test_stats_returns_array(): void
    {
        $s = $this->liga->stats();
        $this->assertCount(4, $s);
        $this->assertArrayHasKey('v', $s[0]);
        $this->assertArrayHasKey('l', $s[0]);
    }

    private function createTestLeague(): array
    {
        return test_create_league('Test Liga', 'Madrid', '2026-06-01', '2026-12-31');
    }
}
