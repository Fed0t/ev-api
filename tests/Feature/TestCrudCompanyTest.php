<?php

namespace Tests\Feature;

use App\Models\Company;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TestCrudCompanyTest extends TestCase
{
    use RefreshDatabase, DatabaseMigrations, WithFaker;

    public function testCompanyFactory()
    {
        $leftRight = Company::getLeftRight();

        $company = Company::factory()->create([
            'name' => $this->faker->name(),
            'left' => $leftRight['left'],
            'right' => $leftRight['right'],
            'parent_id' => 0,
        ]);

        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'name' => $company->name,
        ]);
    }

    public function testCreateCompany()
    {
        $data = [
            'name' => 'Test Company',
        ];

        $response = $this->postJson('/api/v1/companies', $data);

        $response->assertStatus(201);
    }

    public function testUpdateCompany()
    {

        $leftRight = Company::getLeftRight();
        $company = Company::factory()->create([
            'name' => $this->faker->name(),
            'left' => $leftRight['left'],
            'right' => $leftRight['right'],
            'parent_id' => 0,
        ]);

        $data = [
            'name' => 'Test Company',
            'address' => 'Company charger nr 1',
        ];

        $response = $this->putJson('/api/v1/companies/'.$company->id, $data);

        $response->assertStatus(200);
    }

    public function addChildNodeTest()
    {
        $leftRight = Company::getLeftRight();
        $company = Company::factory()->create([
            'name' => $this->faker->name(),
            'left' => $leftRight['left'],
            'right' => $leftRight['right'],
        ]);

        $leftRight = Company::getLeftRight();
        $company2 = Company::factory()->create([
            'name' => $this->faker->name(),
            'left' => $leftRight['left'],
            'right' => $leftRight['right'],
        ]);

        $leftRight = Company::getLeftRight();
        $company3 = Company::factory()->create([
            'name' => $this->faker->name(),
            'left' => $leftRight['left'],
            'right' => $leftRight['right'],
        ]);
        $data = [
            'add_company' => $company2->id,
        ];
        $response = $this->putJson('/api/v1/companies/'.$company->id, $data);
        $data = [
            'add_company' => $company3->id,
        ];

        $response = $this->putJson('/api/v1/companies/'.$company2->id, $data);

        $response->assertStatus(201)->assertJsonFragment([
            'data' => [
                [
                    'id' => $company->id,
                    'name' => $company->name,
                    'descendants' => [
                        [
                            'id' => $company2->id,
                            'name' => $company2->name,
                            'descendants' => [],
                        ],
                        [
                            'id' => $company3->id,
                            'name' => $company3->name,
                            'descendants' => [],
                        ],
                    ],
                ],
                [
                    'id' => $company2->id,
                    'name' => $company2->name,
                    'descendants' => [
                        [
                            'id' => $company3->id,
                            'name' => $company3->name,
                            'descendants' => [],
                        ],
                    ],
                ],
                [
                    'id' => $company3->id,
                    'name' => $company3->name,
                    'descendants' => [],
                ],
            ],
        ]);
    }

    public function deleteUpdateCompany()
    {
        $leftRight = Company::getLeftRight();
        $company = Company::factory()->create([
            'name' => $this->faker->name(),
            'left' => $leftRight['left'],
            'right' => $leftRight['right'],
            'parent_id' => 0,
        ]);

        $response = $this->delete('/api/v1/companies/'.$company->id);

        $response->assertStatus(201);
    }
}
