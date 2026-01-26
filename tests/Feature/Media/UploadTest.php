<?php

namespace Tests\Feature\Media;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_resort_owner_can_upload_temporary_file()
    {
        Storage::fake('public');
        $user = User::factory()->resortOwner()->create();

        $file = UploadedFile::fake()->image('resort.jpg');

        $response = $this->actingAs($user)
            ->postJson('/api/v1/resort-owner/uploads', [
                'file' => $file,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data' => ['temp_id', 'url', 'name', 'mime_type']]);

        $this->assertDatabaseHas('temporary_uploads', [
            'id' => $response->json('data.temp_id'),
        ]);
    }

    public function test_upload_validates_file_type()
    {
        $user = User::factory()->resortOwner()->create();
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($user)
            ->postJson('/api/v1/resort-owner/uploads', [
                'file' => $file,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }
}
