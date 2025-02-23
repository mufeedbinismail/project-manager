<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\AuthenticatesUser;

class ProjectUserTest extends TestCase
{
    use AuthenticatesUser, RefreshDatabase;

    /**
     * Test that a user can assign users to a project successfully.
     *
     * @return void
     */
    public function test_user_can_assign_users_to_project_successfully()
    {
        $project = Project::factory()->create();
        $users = User::factory(3)->create();

        $response = $this->withAuthorizationHeader()
            ->postJson(route('projects.assign-users', $project->id), [
                'user_ids' => $users->pluck('id')->toArray(),
            ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Users assigned to project successfully.']);

        $this->assertCount(3, $project->users);
        $project->forceDelete();
        $users->map->forceDelete();
    }

    /**
     * Test that a user cannot assign users to a project with invalid user IDs.
     *
     * @return void
     */
    public function test_user_cannot_assign_users_to_project_with_invalid_user_ids()
    {
        $project = Project::factory()->create();

        $maxId = User::max('id');

        $response = $this->withAuthorizationHeader()
            ->postJson(route('projects.assign-users', $project->id), [
                'user_ids' => [$maxId + 20, $maxId + 21], // Invalid user IDs
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_ids.0', 'user_ids.1']);

        $project->forceDelete();
    }

    /**
     * Test that a user can unassign users from a project successfully.
     *
     * @return void
     */
    public function test_user_can_unassign_users_from_project_successfully()
    {
        $project = Project::factory()->create();
        $users = User::factory(3)->create();
        $project->users()->attach($users->pluck('id')->toArray());

        $response = $this->withAuthorizationHeader()
            ->deleteJson(route('projects.unassign-users', $project->id), [
                'user_ids' => $users->pluck('id')->toArray(),
            ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Users unassigned from project successfully.']);

        $this->assertCount(0, $project->users);
        $project->forceDelete();
        $users->map->forceDelete();
    }

    /**
     * Test that a user cannot unassign users from a project with invalid user IDs.
     *
     * @return void
     */
    public function test_user_cannot_unassign_users_from_project_with_invalid_user_ids()
    {
        $project = Project::factory()->create();

        $maxId = User::max('id');

        $response = $this->withAuthorizationHeader()
            ->deleteJson(route('projects.unassign-users', $project->id), [
                'user_ids' => [$maxId + 20, $maxId + 21], // Invalid user IDs
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_ids.0', 'user_ids.1']);

        $project->forceDelete();
    }
}
