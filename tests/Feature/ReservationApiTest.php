<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\Resource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resource = Resource::create(['name' => 'Room', 'type' => 'T']);
    }

    public function test_cannot_create_overlapping_reservation()
    {
        Reservation::create([
            'resource_id' => $this->resource->id,
            'start' => '2026-06-01 10:00:00',
            'end' => '2026-06-01 12:00:00',
            'first_name' => 'J', 'last_name' => 'D', 'phone_number' => '1', 'email' => 'j@e.c'
        ]);

        $response = $this->postJson('/api/reservation/create-reservation', [
            'resourceId' => $this->resource->id,
            'start' => '2026-06-01 11:00:00',
            'end' => '2026-06-01 13:00:00',
            'firstName' => 'A', 'lastName' => 'W', 'phoneNumber' => '2', 'email' => 'a@e.c'
        ]);

        $response->assertStatus(409);
    }

    public function test_can_move_reservation()
    {
        $res = Reservation::create([
            'resource_id' => $this->resource->id,
            'start' => '2026-06-01 10:00:00',
            'end' => '2026-06-01 12:00:00',
            'first_name' => 'J', 'last_name' => 'D', 'phone_number' => '1', 'email' => 'j@e.c'
        ]);

        $response = $this->postJson('/api/reservation/move-reservation', [
            'id' => $res->id,
            'newStart' => '2026-06-01 08:00:00',
            'newEnd' => '2026-06-01 10:00:00'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('reservations', [
            'id' => $res->id,
            'start' => '2026-06-01 08:00:00'
        ]);
    }

    public function test_status_transitions()
    {
        $res = Reservation::create([
            'resource_id' => $this->resource->id,
            'start' => '2026-06-01 10:00:00',
            'end' => '2026-06-01 12:00:00',
            'first_name' => 'J', 'last_name' => 'D', 'phone_number' => '1', 'email' => 'j@e.c'
        ]);

        $this->postJson("/api/reservation/ongoing-reservation?id={$res->id}")->assertStatus(200);
        $this->assertEquals('ongoing', $res->fresh()->status);

        $this->postJson("/api/reservation/done-reservation?id={$res->id}")->assertStatus(200);
        $this->assertEquals('done', $res->fresh()->status);

        $this->postJson("/api/reservation/cancel-reservation?id={$res->id}")->assertStatus(200);
        $this->assertEquals('cancelled', $res->fresh()->status);
    }
}
