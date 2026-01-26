<?php

namespace Tests\Feature\Admin;

use App\Enums\GeneralStatus;
use App\Enums\UserType;
use App\Models\Resort;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['type' => UserType::Admin]);
        $resort = Resort::factory()->create();
        $this->unit = Unit::factory()->create(['resort_id' => $resort->id]);
    }

    public function test_admin_can_list_units()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/admin/units');

        $response->assertOk()
            ->assertJsonStructure(['success', 'message', 'data', 'links', 'meta'])
            ->assertJsonFragment(['id' => $this->unit->id]);
    }

    public function test_admin_can_filter_units_by_status()
    {
        $inactiveUnit = Unit::factory()->create(['status' => GeneralStatus::Inactive]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/admin/units?filter[status]=inactive');

        $response->assertOk()
            ->assertJsonFragment(['id' => $inactiveUnit->id])
            ->assertJsonMissing(['id' => $this->unit->id]); // Because current unit is Active by default
    }

    public function test_admin_can_update_unit_status()
    {
        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/admin/units/{$this->unit->id}", [
                'status' => 'inactive',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'inactive');

        $this->assertDatabaseHas('units', [
            'id' => $this->unit->id,
            'status' => 'inactive',
        ]);
    }

    public function test_admin_cannot_update_unit_with_invalid_status()
    {
        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/admin/units/{$this->unit->id}", [
                'status' => 'invalid_status',
            ]);

        $response->assertUnprocessable();
    }
}
