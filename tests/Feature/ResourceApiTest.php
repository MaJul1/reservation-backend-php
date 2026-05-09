<?php

namespace Tests\Feature;

use App\Models\Resource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResourceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_resource()
    {
        $response = $this->postJson('/api/resource', [
            'name' => 'Room A',
            'type' => 'Room',
            'description' => 'Desc'
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('name', 'Room A');

        $this->assertDatabaseHas('resources', ['name' => 'Room A']);
    }

    public function test_can_list_resources_with_pagination()
    {
        Resource::factory()->count(15)->create();

        $response = $this->getJson('/api/resource/get-resources?size=5&page=2');

        $response->assertStatus(200)
                 ->assertJsonCount(5, 'data')
                 ->assertJsonPath('current_page', 2);
    }

    public function test_can_get_resource_by_id()
    {
        $resource = Resource::create(['name' => 'Unique', 'type' => 'T']);

        $response = $this->getJson("/api/resource/get-resrouce-by-id/{$resource->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('name', 'Unique');
    }
}
