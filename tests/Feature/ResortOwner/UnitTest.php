<?php

namespace Tests\Feature\ResortOwner;

use App\Enums\ResortStatus;
use App\Enums\UserType;
use App\Models\Resort;
use App\Models\TemporaryUpload;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_list_units_for_their_resort()
    {
        $owner = User::factory()->resortOwner()->create();
        $resort = Resort::factory()->create(['owner_id' => $owner->id]);
        Unit::factory()->count(3)->create(['resort_id' => $resort->id]);

        $response = $this->actingAs($owner)
            ->getJson("/api/v1/resort-owner/resorts/{$resort->id}/units");

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_owner_can_create_unit_for_their_resort()
    {
        $owner = User::factory()->resortOwner()->create();
        $resort = Resort::factory()->create(['owner_id' => $owner->id]);

        $payload = [
            'ar_name' => 'غرفة ديلوكس',
            'en_name' => 'Deluxe Room',
            'ar_description' => 'وصف عربي',
            'en_description' => 'English description',
            'price_per_night' => 150.00,
            'capacity' => 4,
            'room_count' => 2,
            'features' => ['wifi', 'breakfast_included'],
        ];

        $response = $this->actingAs($owner)
            ->postJson("/api/v1/resort-owner/resorts/{$resort->id}/units", $payload);

        $response->assertCreated()
            ->assertJsonPath('data.en_name', 'Deluxe Room');

        $this->assertDatabaseHas('units', ['en_name' => 'Deluxe Room', 'resort_id' => $resort->id]);
    }

    public function test_owner_cannot_create_unit_for_others_resort()
    {
        $owner = User::factory()->resortOwner()->create();
        $otherOwner = User::factory()->resortOwner()->create();
        $otherResort = Resort::factory()->create(['owner_id' => $otherOwner->id]);

        $payload = [
            'ar_name' => 'غرفة',
            'en_name' => 'Room',
            'price_per_night' => 100.00,
            'capacity' => 2,
        ];

        $response = $this->actingAs($owner)
            ->postJson("/api/v1/resort-owner/resorts/{$otherResort->id}/units", $payload);

        $response->assertForbidden();
    }

    public function test_owner_can_update_their_unit()
    {
        $owner = User::factory()->resortOwner()->create();
        $resort = Resort::factory()->create(['owner_id' => $owner->id]);
        $unit = Unit::factory()->create(['resort_id' => $resort->id]);

        $response = $this->actingAs($owner)
            ->putJson("/api/v1/resort-owner/resorts/{$resort->id}/units/{$unit->id}", [
                'en_name' => 'Updated Room',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.en_name', 'Updated Room');
    }

    public function test_owner_can_delete_their_unit()
    {
        $owner = User::factory()->resortOwner()->create();
        $resort = Resort::factory()->create(['owner_id' => $owner->id]);
        $unit = Unit::factory()->create(['resort_id' => $resort->id]);

        $response = $this->actingAs($owner)
            ->deleteJson("/api/v1/resort-owner/resorts/{$resort->id}/units/{$unit->id}");

        $response->assertOk()
             ->assertJson(['success' => true]);
        $this->assertDatabaseMissing('units', ['id' => $unit->id]);
    }
}
