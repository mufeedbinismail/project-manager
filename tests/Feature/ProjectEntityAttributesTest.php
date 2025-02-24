<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Tests\TestCase;
use Tests\Traits\AuthenticatesUser;

class ProjectEntityAttributesTest extends TestCase
{
    use RefreshDatabase, AuthenticatesUser;

    protected $textAttribute;
    protected $numberAttribute;
    protected $dateAttribute;
    protected $selectAttribute;

    protected function setUp(): void
    {
        parent::setUp();

        // Create some attributes for testing
        $this->textAttribute = Attribute::create([
            'name' => 'Priority',
            'type' => Attribute::TYPE_TEXT,
        ]);

        $this->numberAttribute = Attribute::create([
            'name' => 'Budget',
            'type' => Attribute::TYPE_NUMBER,
        ]);

        $this->dateAttribute = Attribute::create([
            'name' => 'Due Date',
            'type' => Attribute::TYPE_DATE,
        ]);

        $this->selectAttribute = Attribute::create([
            'name' => 'Status',
            'type' => Attribute::TYPE_SELECT,
        ]);

        $this->selectAttribute->possibleValues()->createMany([
            ['key' => 'open', 'value' => 'Open'],
            ['key' => 'closed', 'value' => 'Closed'],
        ]);

        $this->initializeAuthentication();
    }

    /** @test */
    public function it_can_store_entity_attributes_when_creating_a_project()
    {
        $response = $this->withAuthorizationHeader()->postJson(route('projects.store'), [
            'name' => 'Test Project',
            'status' => Arr::random(Project::getStatuses()),
            'attributes' => [
                [
                    'attribute_id' => $this->textAttribute->id,
                    'value' => 'High',
                ],
                [
                    'attribute_id' => $this->numberAttribute->id,
                    'value' => 1000,
                ],
                [
                    'attribute_id' => $this->dateAttribute->id,
                    'value' => '2025-12-31',
                ],
                [
                    'attribute_id' => $this->selectAttribute->id,
                    'value' => 'open',
                ],
            ],
        ]);

        $response->assertStatus(201);

        $projectId = $response->json('id');

        $this->assertDatabaseHas('attribute_values', [
            'entity_id' => $projectId,
            'attribute_id' => $this->textAttribute->id,
            'value' => 'High',
        ]);

        $this->assertDatabaseHas('attribute_values', [
            'entity_id' => $projectId,
            'attribute_id' => $this->numberAttribute->id,
            'value' => 1000,
        ]);

        $this->assertDatabaseHas('attribute_values', [
            'entity_id' => $projectId,
            'attribute_id' => $this->dateAttribute->id,
            'value' => '2025-12-31',
        ]);

        $this->assertDatabaseHas('attribute_values', [
            'entity_id' => $projectId,
            'attribute_id' => $this->selectAttribute->id,
            'value' => 'open',
        ]);
    }

    /** @test */
    public function it_can_filter_projects_by_dynamic_attribute_values()
    {
        $project1 = Project::create([
            'name' => 'Project 1',
            'status' => Arr::random(Project::getStatuses()),
        ]);

        $project1->setEntityAttributes([
            [
                'attribute_id' => $this->textAttribute->id,
                'value' => 'High',
            ],
            [
                'attribute_id' => $this->numberAttribute->id,
                'value' => 1000,
            ],
        ]);

        $project2 = Project::create([
            'name' => 'Project 2',
            'status' => Arr::random(Project::getStatuses()),
        ]);

        $project2->setEntityAttributes([
            [
                'attribute_id' => $this->textAttribute->id,
                'value' => 'Medium',
            ],
            [
                'attribute_id' => $this->numberAttribute->id,
                'value' => 2000,
            ],
        ]);

        // Test filtering by exact match
        $response = $this->withAuthorizationHeader()
            ->getJson(route('projects.index') . '?' . http_build_query(['filters' => ['Priority' => 'High', 'Budget' => 1000]]));
        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['name' => 'Project 1']);

        // Test filtering by greater than
        $response = $this->withAuthorizationHeader()
            ->getJson(route('projects.index') . '?' . http_build_query(['filters' => ['Budget' => ['>=' => 1000]]]));
        $response->assertStatus(200);
        $response->assertJsonCount(2);

        // Test filtering by less than
        $response = $this->withAuthorizationHeader()
            ->getJson(route('projects.index') . '?' . http_build_query(['filters' => ['Budget' => ['<' => 2000]]]));
        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['name' => 'Project 1']);

        // Test filtering by not equal
        $response = $this->withAuthorizationHeader()
            ->getJson(route('projects.index') . '?' . http_build_query(['filters' => ['Priority' => ['!=' => 'High']]]));
        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['name' => 'Project 2']);

        // Test validation for invalid operator
        $response = $this->withAuthorizationHeader()
            ->getJson(route('projects.index') . '?' . http_build_query(['filters' => ['Priority' => ['invalid' => 'High']]]));
        $response->assertStatus(422);

        // Test validation for invalid value
        $response = $this->withAuthorizationHeader()
            ->getJson(route('projects.index') . '?' . http_build_query(['filters' => ['Priority' => ['=' => '']]]));
        $response->assertStatus(422);
    }
}