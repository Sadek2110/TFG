<?php

use PHPUnit\Framework\TestCase;

class CampoTest extends TestCase
{
    private Campo $campo;

    protected function setUp(): void
    {
        test_reset();
        $this->campo = new Campo();
    }

    // ===== create() =====
    public function test_create_field(): void
    {
        [$field, $errors] = $this->campo->create([
            'name'        => 'La Cantera',
            'city'        => 'Madrid',
            'address'     => 'Calle Test 1',
            'surface'     => 'césped',
            'capacity'    => '22',
            'hourly_rate' => '35.00',
        ]);

        $this->assertEmpty($errors);
        $this->assertNotNull($field);
        $this->assertSame('La Cantera', $field['name']);
        $this->assertSame('césped', $field['surface']);
        $this->assertSame(22, (int) $field['capacity']);
        $this->assertSame(35.0, (float) $field['hourly_rate']);
    }

    public function test_create_field_with_defaults(): void
    {
        [$field, $errors] = $this->campo->create([
            'name' => 'Campo Simple',
            'city' => 'Valencia',
        ]);

        $this->assertEmpty($errors);
        $this->assertSame('césped', $field['surface']);
        $this->assertSame(22, (int) $field['capacity']);
        $this->assertSame(0.0, (float) $field['hourly_rate']);
    }

    public function test_create_field_missing_name(): void
    {
        [, $errors] = $this->campo->create(['name' => '', 'city' => 'Madrid']);
        $this->assertArrayHasKey('name', $errors);
    }

    public function test_create_field_missing_city(): void
    {
        [, $errors] = $this->campo->create(['name' => 'Test', 'city' => '']);
        $this->assertArrayHasKey('city', $errors);
    }

    public function test_create_field_invalid_surface(): void
    {
        [, $errors] = $this->campo->create([
            'name' => 'Test', 'city' => 'Madrid', 'surface' => 'hielo',
        ]);
        $this->assertArrayHasKey('surface', $errors);
    }

    public function test_create_field_capacity_out_of_range(): void
    {
        [, $errors] = $this->campo->create([
            'name' => 'Test', 'city' => 'Madrid', 'capacity' => '2',
        ]);
        $this->assertArrayHasKey('capacity', $errors);

        [, $errors2] = $this->campo->create([
            'name' => 'Test', 'city' => 'Madrid', 'capacity' => '100',
        ]);
        $this->assertArrayHasKey('capacity', $errors2);
    }

    public function test_create_field_capacity_at_limits(): void
    {
        [$field4] = $this->campo->create(['name' => 'F4', 'city' => 'M', 'capacity' => '4']);
        $this->assertNotNull($field4);

        [$field50] = $this->campo->create(['name' => 'F50', 'city' => 'M', 'capacity' => '50']);
        $this->assertNotNull($field50);
    }

    public function test_create_field_rate_out_of_range(): void
    {
        [, $errors] = $this->campo->create([
            'name' => 'Test', 'city' => 'Madrid', 'hourly_rate' => '-1',
        ]);
        $this->assertArrayHasKey('hourly_rate', $errors);

        [, $errors2] = $this->campo->create([
            'name' => 'Test', 'city' => 'Madrid', 'hourly_rate' => '1001',
        ]);
        $this->assertArrayHasKey('hourly_rate', $errors2);
    }

    // ===== find() =====
    public function test_find_field(): void
    {
        $f = test_create_field('Find Campo', 'Barcelona');
        $found = $this->campo->find((int) $f['id']);
        $this->assertNotNull($found);
        $this->assertSame('Find Campo', $found['name']);
    }

    public function test_find_nonexistent(): void
    {
        $this->assertNull($this->campo->find(9999));
    }

    // ===== all() =====
    public function test_all_fields(): void
    {
        test_create_field('Campo 1', 'Madrid');
        test_create_field('Campo 2', 'Barcelona');
        $all = $this->campo->all();
        $this->assertCount(2, $all);
    }

    public function test_all_sorted_by_city_then_name(): void
    {
        test_create_field('Z Campo', 'Madrid');
        test_create_field('A Campo', 'Barcelona');
        $all = $this->campo->all();
        $this->assertSame('Barcelona', $all[0]['city']);
        $this->assertSame('Madrid', $all[1]['city']);
    }

    // ===== delete() =====
    public function test_delete_field(): void
    {
        $f = test_create_field();
        $this->campo->delete((int) $f['id']);
        $this->assertNull($this->campo->find((int) $f['id']));
    }
}
