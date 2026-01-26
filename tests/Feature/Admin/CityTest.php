<?php

namespace Tests\Feature\Admin;

use App\Enums\GeneralStatus;
use App\Enums\UserType;
use App\Models\Area;
use App\Models\City;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

describe('Admin City Management', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create(['type' => UserType::Admin]);
        $this->user = User::factory()->create(['type' => UserType::User]);
    });

    it('allows admin to list cities with pagination', function () {
        City::create(['ar_name' => 'Tripoli', 'en_name' => 'Tripoli', 'status' => GeneralStatus::Active]);
        
        Sanctum::actingAs($this->admin);

        $this->getJson('/api/v1/admin/cities')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonStructure(['success', 'message', 'data', 'links', 'meta'])
            ->assertJsonFragment(['en_name' => 'Tripoli']);
    });

    it('allows admin to filter cities by status', function () {
        City::create(['ar_name' => 'Active', 'en_name' => 'Active', 'status' => GeneralStatus::Active]);
        City::create(['ar_name' => 'Inactive', 'en_name' => 'Inactive', 'status' => GeneralStatus::Inactive]);
        
        Sanctum::actingAs($this->admin);

        $this->getJson('/api/v1/admin/cities?filter[status]=active')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['en_name' => 'Active'])
            ->assertJsonMissing(['en_name' => 'Inactive']);
    });

    it('allows admin to search cities', function () {
        City::create(['ar_name' => 'Tripoli', 'en_name' => 'Tripoli', 'status' => GeneralStatus::Active]);
        City::create(['ar_name' => 'Benghazi', 'en_name' => 'Benghazi', 'status' => GeneralStatus::Active]);
        
        Sanctum::actingAs($this->admin);

        $this->getJson('/api/v1/admin/cities?filter[search]=tri')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['en_name' => 'Tripoli'])
            ->assertJsonMissing(['en_name' => 'Benghazi']);
    });

    it('forbids non-admin from listing cities', function () {
        Sanctum::actingAs($this->user);

        $this->getJson('/api/v1/admin/cities')
            ->assertForbidden(); // Assuming middleware checks admin role, or route is only for admin prefix?? 
            // Wait, currently route is just auth:sanctum under /admin prefix. 
            // The controller DOES NOT check role yet? Let's check Admin/AuthController login... 
            // Ah, login checks role, but the middleware on the route group is just 'auth:sanctum'.
            // A regular user with a token *could* technically access /api/v1/admin/cities if we don't add a role middleware.
            // But usually we should middleware this. For now, let's assume implementation logic or middleware.
            // ACTUALLY, I should add a CheckRole middleware or check in controller? 
            // The prompt didn't strictly ask for it but good practice.
            // Let's assume for now passing simply auth checks, and I will fix if fails.
            // Wait, standard practice is to separate routes by role.
    });

    it('allows admin to create a city', function () {
        Sanctum::actingAs($this->admin);

        $payload = [
            'ar_name' => 'Benghazi',
            'en_name' => 'Benghazi',
            'status' => 'active',
        ];

        $this->postJson('/api/v1/admin/cities', $payload)
            ->assertCreated()
            ->assertJsonPath('data.en_name', 'Benghazi');

        $this->assertDatabaseHas('cities', ['en_name' => 'Benghazi']);
    });

    it('validates city creation', function () {
        Sanctum::actingAs($this->admin);

        $this->postJson('/api/v1/admin/cities', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['ar_name', 'en_name', 'status']);
    });

    it('allows admin to update a city', function () {
        $city = City::create(['ar_name' => 'Old', 'en_name' => 'Old', 'status' => GeneralStatus::Active]);
        Sanctum::actingAs($this->admin);

        $this->putJson("/api/v1/admin/cities/{$city->id}", [
            'ar_name' => 'New',
            'en_name' => 'New',
            'status' => 'inactive',
        ])
            ->assertOk()
            ->assertJsonPath('data.en_name', 'New');

        $this->assertDatabaseHas('cities', ['id' => $city->id, 'en_name' => 'New', 'status' => 'inactive']);
    });

    it('allows admin to delete a city', function () {
        $city = City::create(['ar_name' => 'Del', 'en_name' => 'Del', 'status' => GeneralStatus::Active]);
        Sanctum::actingAs($this->admin);

        $this->deleteJson("/api/v1/admin/cities/{$city->id}")
            ->assertOk();

        $this->assertDatabaseMissing('cities', ['id' => $city->id]);
    });
});
