<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Tests\TestCase;
use Tests\Traits\AuthenticatesUser;

class ProjectTest extends TestCase
{
    use AuthenticatesUser, RefreshDatabase;

    /**
     * Test that a user can retrieve all projects.
     *
     * @return void
     */
    public function test_user_can_retrieve_all_projects()
    {
        $response = $this->withAuthorizationHeader()
            ->getJson(route('projects.index'));

        $response->assertStatus(200)
            ->assertJsonCount(Project::count());
    }

    /**
     * Test that a user can create a new project.
     *
     * @return void
     */
    public function test_user_can_create_new_project()
    {
        $projectData = Project::factory()->make()->toArray();

        $response = $this->withAuthorizationHeader()
            ->postJson(route('projects.store'), $projectData);

        $response->assertStatus(201)
             ->assertJsonStructure([
                 'id', 'name', 'status', 'created_at', 'updated_at'
             ]);

        $this->assertDatabaseHas('projects', [
            'name' => $projectData['name']
        ]);

        // force delete the project
        Project::find($response->json('id'))->forceDelete();
    }

    /**
     * Test that a user cannot create a new project with invalid inputs.
     *
     * @return void
     */
    public function test_user_cannot_create_new_project_with_invalid_inputs()
    {
        $projectData = [
            'name' => '',
            'status' => 'invalid-status',
        ];

        $response = $this->withAuthorizationHeader()
            ->postJson(route('projects.store'), $projectData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'status']);
    }

    /**
     * Test that a user can retrieve a specific project.
     *
     * @return void
     */
    public function test_user_can_retrieve_specific_project()
    {
        $project = Project::factory()->create();

        $response = $this->withAuthorizationHeader()
            ->getJson(route('projects.show', $project->id));

        $response->assertStatus(200)
            ->assertJson([
                'id' => $project->id,
                'name' => $project->name,
                'status' => $project->status
            ]);

        $project->forceDelete();
    }

    /**
     * Test that a user can update a specific project.
     *
     * @return void
     */
    public function test_user_can_update_specific_project()
    {
        $project = Project::factory()->create();

        $otherStatuses = Arr::where(Project::getStatuses(), fn ($v) => $v != $project->status);
        $newStatus = Arr::random($otherStatuses);

        $updateData = [
            'name' => 'Updated Project Name',
            'status' => $newStatus,
        ];

        $response = $this->withAuthorizationHeader()
            ->putJson(route('projects.update', $project->id), $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $project->id,
                'name' => 'Updated Project Name',
                'status' => $newStatus,
            ]);

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Updated Project Name',
            'status' => $newStatus,
        ]);

        $project->forceDelete();
    }

    /**
     * Test that a user cannot update a specific project with invalid inputs.
     *
     * @return void
     */
    public function test_user_cannot_update_specific_project_with_invalid_inputs()
    {
        $project = Project::factory()->create();

        $updateData = [
            'name' => '',
            'status' => 'invalid-status',
        ];

        $response = $this->withAuthorizationHeader()
            ->putJson(route('projects.update', $project->id), $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'status']);

        $project->forceDelete();
    }

    /**
     * Test that a user can delete a specific project.
     *
     * @return void
     */
    public function test_user_can_delete_specific_project()
    {
        $project = Project::factory()->create();

        $response = $this->withAuthorizationHeader()
            ->deleteJson(route('projects.destroy', $project->id));

        $response->assertStatus(204);

        $this->assertSoftDeleted($project);
    }
}
