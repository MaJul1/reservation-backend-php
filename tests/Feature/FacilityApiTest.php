<?php

namespace Tests\Feature;

use App\Models\Facility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacilityApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_facility()
    {
        $response = $this->postJson('/api/facility', [
            'name' => 'Hall A',
            'type' => 'Hall',
            'description' => 'Desc',
            'capacity' => 50,
            'location' => 'Building 1, Floor 2'
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('name', 'Hall A')
                 ->assertJsonPath('location', 'Building 1, Floor 2');

        $this->assertDatabaseHas('facilities', ['name' => 'Hall A', 'location' => 'Building 1, Floor 2']);
    }

    public function test_can_list_facilities_with_pagination()
    {
        Facility::factory()->count(15)->create();

        $response = $this->getJson('/api/facility/get-facilities?size=5&page=2');

        $response->assertStatus(200)
                 ->assertJsonCount(5, 'data')
                 ->assertJsonPath('current_page', 2);
    }

    public function test_can_get_facility_by_id()
    {
        $facility = Facility::factory()->create(['name' => 'Unique']);

        $response = $this->getJson("/api/facility/get-facility-by-id/{$facility->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('name', 'Unique');
    }
}
