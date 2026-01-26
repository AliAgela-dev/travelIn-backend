<?php

namespace Tests\Feature\Admin;

use App\Enums\GeneralStatus;
use App\Enums\UserType;
use App\Models\Area;
use App\Models\City;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

describe('Admin Area Management', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create(['type' => UserType::Admin]);
        $this->city = City::create(['ar_name' => 'Tripoli', 'en_name' => 'Tripoli', 'status' => GeneralStatus::Active]);
    });

    it('allows admin to list areas with pagination', function () {
        Area::create(['city_id' => $this->city->id, 'ar_name' => 'Area1', 'en_name' => 'Area1', 'status' => GeneralStatus::Active]);
        
        Sanctum::actingAs($this->admin);

        $this->getJson('/api/v1/admin/areas')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonStructure(['success', 'message', 'data', 'links', 'meta']);
    });

    it('allows admin to filter areas by city', function () {
        $otherCity = City::create(['ar_name' => 'Other', 'en_name' => 'Other', 'status' => GeneralStatus::Active]);
        Area::create(['city_id' => $this->city->id, 'ar_name' => 'Area1', 'en_name' => 'Area1']);
        Area::create(['city_id' => $otherCity->id, 'ar_name' => 'Area2', 'en_name' => 'Area2']);
        
        Sanctum::actingAs($this->admin);

        $this->getJson("/api/v1/admin/areas?filter[city_id]={$this->city->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['en_name' => 'Area1'])
            ->assertJsonMissing(['en_name' => 'Area2']);
    });

    it('allows admin to search areas', function () {
        Area::create(['city_id' => $this->city->id, 'ar_name' => 'Gargaresh', 'en_name' => 'Gargaresh']);
        Area::create(['city_id' => $this->city->id, 'ar_name' => 'Andalus', 'en_name' => 'Andalus']);
        
        Sanctum::actingAs($this->admin);

        $this->getJson('/api/v1/admin/areas?filter[search]=gar')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['en_name' => 'Gargaresh'])
            ->assertJsonMissing(['en_name' => 'Andalus']);
    });

    it('allows admin to create an area', function () {
        Sanctum::actingAs($this->admin);

        $payload = [
            'city_id' => $this->city->id,
            'ar_name' => 'Gargaresh',
            'en_name' => 'Gargaresh',
            'status' => 'active',
        ];

        $this->postJson('/api/v1/admin/areas', $payload)
            ->assertCreated()
            ->assertJsonPath('data.en_name', 'Gargaresh');

        $this->assertDatabaseHas('areas', ['en_name' => 'Gargaresh']);
    });

    it('allows admin to update an area', function () {
        $area = Area::create(['city_id' => $this->city->id, 'ar_name' => 'Old', 'en_name' => 'Old', 'status' => GeneralStatus::Active]);
        Sanctum::actingAs($this->admin);

        $this->putJson("/api/v1/admin/areas/{$area->id}", [
            'city_id' => $this->city->id,
            'ar_name' => 'New',
            'en_name' => 'New',
            'status' => 'inactive',
        ])
            ->assertOk();

        $this->assertDatabaseHas('areas', ['id' => $area->id, 'en_name' => 'New', 'status' => 'inactive']);
    });

    it('allows admin to delete an area', function () {
        $area = Area::create(['city_id' => $this->city->id, 'ar_name' => 'Del', 'en_name' => 'Del', 'status' => GeneralStatus::Active]);
        Sanctum::actingAs($this->admin);

        $this->deleteJson("/api/v1/admin/areas/{$area->id}")
            ->assertOk();

        $this->assertDatabaseMissing('areas', ['id' => $area->id]);
    });
});
