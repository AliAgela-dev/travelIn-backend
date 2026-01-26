<?php

use App\Models\User;
use App\Enums\UserType;
use App\Enums\UserStatus;
use Laravel\Sanctum\Sanctum;

describe('Authenticated User', function () {
    it('authenticated user can access /me endpoint', function () {
        $user = User::factory()->create([
            'type' => UserType::User,
            'status' => UserStatus::Active,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/user/me');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user' => ['id', 'full_name', 'phone_number', 'type', 'status'],
                ],
            ]);
    });

    it('unauthenticated request to /me returns 401', function () {
        $response = $this->getJson('/api/v1/user/me');

        $response->assertStatus(401);
    });

    it('logout invalidates the token', function () {
        $user = User::factory()->create([
            'type' => UserType::User,
            'status' => UserStatus::Active,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/user/logout');

        $response->assertOk()
            ->assertJson(['message' => 'Logged out successfully.']);
    });

    it('me returns correct user data', function () {
        $user = User::factory()->create([
            'full_name' => 'John Doe',
            'phone_number' => '218912345678',
            'type' => UserType::User,
            'status' => UserStatus::Active,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/user/me');

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'user' => [
                        'full_name' => 'John Doe',
                        'phone_number' => '218912345678',
                        'type' => 'user',
                        'status' => 'active',
                    ],
                ],
            ]);
    });

    it('me returns user with city data', function () {
        $city = \App\Models\City::create([
            'ar_name' => 'طرابلس',
            'en_name' => 'Tripoli',
        ]);

        $user = User::factory()->create([
            'city_id' => $city->id,
            'type' => UserType::User,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/user/me');

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'user' => [
                        'city' => [
                            'id' => $city->id,
                            'ar_name' => 'طرابلس',
                            'en_name' => 'Tripoli',
                        ],
                    ],
                ],
            ]);
    });
});
