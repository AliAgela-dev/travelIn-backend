<?php

namespace Tests\Feature\ResortOwner;

use App\Enums\ResortStatus;
use App\Models\Area;
use App\Models\City;
use App\Models\Resort;
use App\Models\TemporaryUpload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResortTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_owner_can_create_resort_with_media()
    {
        Storage::fake('public');
        $owner = User::factory()->resortOwner()->create();
        $city = City::factory()->create();
        $area = Area::factory()->create(['city_id' => $city->id]);

        // Create Temp Upload
        $tempUpload = TemporaryUpload::factory()->create();
        $file = UploadedFile::fake()->image('resort.jpg');
        $tempUpload->addMedia($file)->toMediaCollection('default');

        $data = [
            'city_id' => $city->id,
            'area_id' => $area->id,
            'ar_name' => 'منتجع جميل',
            'en_name' => 'Beautiful Resort',
            'ar_description' => 'وصف',
            'en_description' => 'Description',
            'media_ids' => [$tempUpload->id],
        ];

        $response = $this->actingAs($owner)
            ->postJson('/api/v1/resort-owner/resorts', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', ResortStatus::Pending->value)
            ->assertJsonPath('data.en_name', 'Beautiful Resort');

        $this->assertDatabaseHas('resorts', [
            'owner_id' => $owner->id,
            'en_name' => 'Beautiful Resort',
            'status' => ResortStatus::Pending,
        ]);

        // Verify media attached
        $resort = Resort::first();
        $this->assertTrue($resort->getMedia('default')->isNotEmpty());
    }

    public function test_owner_can_list_own_resorts()
    {
        $owner = User::factory()->resortOwner()->create();
        Resort::factory()->count(3)->create(['owner_id' => $owner->id]);
        
        // Other owner's resort
        Resort::factory()->create();

        $response = $this->actingAs($owner)
            ->getJson('/api/v1/resort-owner/resorts');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_owner_can_update_resort_but_not_status()
    {
        $owner = User::factory()->resortOwner()->create();
        $resort = Resort::factory()->create(['owner_id' => $owner->id, 'status' => ResortStatus::Pending]);

        $response = $this->actingAs($owner)
            ->putJson("/api/v1/resort-owner/resorts/{$resort->id}", [
                'en_name' => 'Updated Name',
                'status' => ResortStatus::Active->value, // Should be ignored
            ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('resorts', [
            'id' => $resort->id,
            'en_name' => 'Updated Name',
            'status' => ResortStatus::Pending->value, // Still pending
        ]);
    }

    public function test_owner_cannot_access_others_resort()
    {
        $owner = User::factory()->resortOwner()->create();
        $otherResort = Resort::factory()->create();

        $this->actingAs($owner)
            ->getJson("/api/v1/resort-owner/resorts/{$otherResort->id}")
            ->assertStatus(403);
            
        $this->actingAs($owner)
            ->putJson("/api/v1/resort-owner/resorts/{$otherResort->id}", ['en_name' => 'Hack'])
            ->assertStatus(403);
            
        $this->actingAs($owner)
            ->deleteJson("/api/v1/resort-owner/resorts/{$otherResort->id}")
            ->assertStatus(403);
    }
}
