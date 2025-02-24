<?php

namespace Tests\Feature;

use App\Models\Timesheet;
use App\Models\User;
use App\Models\Project;
use Tests\TestCase;
use Tests\Traits\AuthenticatesUser;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TimesheetTest extends TestCase
{
    use RefreshDatabase, AuthenticatesUser;

    /**
     * Test that a user can create a new timesheet.
     *
     * @return void
     */
    public function test_user_can_create_new_timesheet()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $timesheetData = Timesheet::factory()->make([
            'user_id' => $user->id,
            'project_id' => $project->id,
        ])->toArray();

        $response = $this->withAuthorizationHeader()
            ->postJson(route('timesheets.store'), $timesheetData);

        $response->assertStatus(201)
             ->assertJsonStructure([
                 'id', 'user_id', 'project_id', 'task_name', 'date', 'hours', 'created_at', 'updated_at'
             ]);

        $this->assertDatabaseHas('timesheets', [
            'user_id' => $timesheetData['user_id'],
            'project_id' => $timesheetData['project_id'],
            'task_name' => $timesheetData['task_name'],
        ]);

        // force delete the timesheet
        $timesheet = Timesheet::find($response->json('id'));
        $timesheet->user->forceDelete();
        $timesheet->project->forceDelete();
        $timesheet->forceDelete();
    }

    /**
     * Test that a user cannot create a new timesheet with invalid data.
     *
     * @return void
     */
    public function test_user_cannot_create_new_timesheet_with_invalid_data()
    {
        $response = $this->withAuthorizationHeader()
            ->postJson(route('timesheets.store'), [
                'user_id' => 999,
                'project_id' => 999,
                'task_name' => '',
                'date' => 'invalid-date',
                'hours' => 25,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id', 'project_id', 'task_name', 'date', 'hours']);
    }

    /**
     * Test that a user can retrieve a specific timesheet.
     *
     * @return void
     */
    public function test_user_can_retrieve_specific_timesheet()
    {
        $timesheet = Timesheet::factory()->create();

        $response = $this->withAuthorizationHeader()
            ->getJson(route('timesheets.show', $timesheet->id));

        $response->assertStatus(200)
            ->assertJson([
                'id' => $timesheet->id,
                'user_id' => $timesheet->user_id,
                'project_id' => $timesheet->project_id,
                'task_name' => $timesheet->task_name,
                'date' => $timesheet->date,
                'hours' => $timesheet->hours,
            ]);

        $timesheet->user->forceDelete();
        $timesheet->project->forceDelete();
        $timesheet->forceDelete();
    }

    /**
     * Test that a user can update a specific timesheet.
     *
     * @return void
     */
    public function test_user_can_update_specific_timesheet()
    {
        $timesheet = Timesheet::factory()->create();

        $updateData = [
            'task_name' => 'Updated Task Name',
            'hours' => 8,
        ];

        $response = $this->withAuthorizationHeader()
            ->putJson(route('timesheets.update', $timesheet->id), $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $timesheet->id,
                'task_name' => 'Updated Task Name',
                'hours' => 8,
            ]);

        $this->assertDatabaseHas('timesheets', [
            'id' => $timesheet->id,
            'task_name' => 'Updated Task Name',
            'hours' => 8,
        ]);

        $timesheet->user->forceDelete();
        $timesheet->project->forceDelete();
        $timesheet->forceDelete();
    }

    /**
     * Test that a user cannot update a specific timesheet with invalid data.
     *
     * @return void
     */
    public function test_user_cannot_update_specific_timesheet_with_invalid_data()
    {
        $timesheet = Timesheet::factory()->create();

        $response = $this->withAuthorizationHeader()
            ->putJson(route('timesheets.update', $timesheet->id), [
                'task_name' => '',
                'date' => 'invalid-date',
                'hours' => 25,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['task_name', 'date', 'hours']);

        $timesheet->user->forceDelete();
        $timesheet->project->forceDelete();
        $timesheet->forceDelete();
    }

    /**
     * Test that a user can delete a specific timesheet.
     *
     * @return void
     */
    public function test_user_can_delete_specific_timesheet()
    {
        $timesheet = Timesheet::factory()->create();

        $response = $this->withAuthorizationHeader()
            ->deleteJson(route('timesheets.destroy', $timesheet->id));

        $response->assertStatus(204);

        $this->assertSoftDeleted($timesheet);

        $timesheet->user->forceDelete();
        $timesheet->project->forceDelete();
        $timesheet->forceDelete();
    }

    /**
     * Test that a user can retrieve all timesheets.
     *
     * @return void
     */
    public function test_user_can_retrieve_all_timesheets()
    {
        $response = $this->withAuthorizationHeader()
            ->getJson(route('timesheets.index'));

        $response->assertStatus(200)
            ->assertJsonCount(Timesheet::count());
    }
}