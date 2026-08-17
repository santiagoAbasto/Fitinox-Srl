<?php

namespace Tests\Feature;

use App\Models\Distributor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DistributorAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_maps_coordinates_are_accepted_without_pin_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $response = $this->actingAs($admin)->post('/admin/distributors', [
            'country' => 'Argentina',
            'name' => 'Distribuidor de prueba',
            'address' => 'San Lorenzo, Santa Fe',
            'phone' => '',
            'email' => '',
            'latitude' => '-32.7302784904841',
            'longitude' => '60.733186345407354',
            'sort_order' => 'ZZ',
            'is_active' => true,
        ]);

        $response->assertSessionHasNoErrors();

        $distributor = Distributor::where('name', 'Distribuidor de prueba')->firstOrFail();
        $this->assertSame(-32.7302785, $distributor->latitude);
        $this->assertSame(60.7331863, $distributor->longitude);
        $this->assertSame(50, $distributor->marker_x);
        $this->assertSame(50, $distributor->marker_y);
    }

    public function test_a_complete_google_maps_pair_can_be_pasted_into_latitude(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $response = $this->actingAs($admin)->post('/admin/distributors', [
            'country' => 'Argentina',
            'name' => 'Coordenadas pegadas',
            'address' => 'San Lorenzo, Santa Fe',
            'latitude' => '-32.7302784904841, -60.733186345407354',
            'longitude' => '',
            'sort_order' => 'ZY',
            'is_active' => true,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('distributors', [
            'name' => 'Coordenadas pegadas',
            'latitude' => -32.7302785,
            'longitude' => -60.7331863,
        ]);
    }
}
