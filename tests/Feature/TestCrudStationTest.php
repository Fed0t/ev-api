<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Station;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class TestCrudStationTest extends TestCase
{
    use RefreshDatabase, DatabaseMigrations, WithFaker;

    public function testStationFactory()
    {
        $station = Station::factory()->create([
            'name' => $this->faker->name(),
        ]);

        $this->assertDatabaseHas('stations', [
            'id' => $station->id,
            'name' => $station->name,
        ]);
    }

    public function testCreateStation()
    {
        $data = [
            'name' => 'Test Station',
            'address' => 'Station charger nr 1'
        ];

        $response = $this->postJson('/api/v1/stations', $data);

        $response->assertStatus(201)
            ->assertJsonFragment($data);
    }


    public function testUpdateStation()
    {
        $station = Station::factory()->create([
            'name' => $this->faker->name(),
        ]);

        $leftRight = Company::getLeftRight();

        $company = Company::factory()->create([
            'name' => $this->faker->name(),
            'left' => $leftRight['left'],
            'right' => $leftRight['right'],
        ]);

        $data = [
            'name' => 'Test Station 1',
            'address' => 'Station charger nr 1',
            'latitude' => '',
            'longitude' => '',
            'company_id' => $company->id,
        ];

        $response = $this->putJson('/api/v1/stations/'.$station->id, $data);

        $response->assertStatus(200);
    }

    public function testDeleteStation()
    {
        $station = Station::factory()->create([
            'name' => $this->faker->name(),
        ]);

        $response = $this->delete('/api/v1/stations/'.$station->id);

        $response->assertStatus(200);
    }
}
