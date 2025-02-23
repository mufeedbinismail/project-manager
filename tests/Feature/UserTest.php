<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\AuthenticatesUser;

class UserTest extends TestCase
{
    use AuthenticatesUser, RefreshDatabase;
    
    /**
     * Test that a user can retrieve all users.
     *
     * @return void
     */
    public function test_user_can_retrieve_all_users()
    {
        $response = $this->withAuthorizationHeader()
            ->getJson(route('users.index'));

        $response->assertStatus(200)
            ->assertJsonCount(User::count());
    }

    /**
     * Test that a user can create a new user.
     *
     * @return void
     */
    public function test_user_can_create_new_user()
    {
        $userData = User::factory()->make()->toArray();
        $userData['password'] = 'password';
        $userData['password_confirmation'] = 'password';

        $response = $this->withAuthorizationHeader()
            ->postJson(route('users.store'), $userData);

        $response->assertStatus(201)
             ->assertJsonStructure([
                 'id', 'first_name', 'last_name', 'email', 'created_at', 'updated_at'
             ]);

        $this->assertDatabaseHas('users', [
            'email' => $userData['email']
        ]);

        // force delete the user
        User::where('email', $userData['email'])->first()->forceDelete();
    }

    /**
     * Test that a user cannot create a new user with invalid inputs.
     *
     * @return void
     */
    public function test_user_cannot_create_new_user_with_invalid_inputs()
    {
        $userData = [
            'first_name' => '',
            'last_name' => '',
            'email' => 'invalid-email',
            'password' => 'pass',
            'password_confirmation' => 'password'
        ];

        $response = $this->withAuthorizationHeader()
            ->postJson(route('users.store'), $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['first_name', 'last_name', 'email', 'password']);
    }

    /**
     * Test that a user can retrieve a specific user.
     *
     * @return void
     */
    public function test_user_can_retrieve_specific_user()
    {
        $user = User::factory()->create();

        $response = $this->withAuthorizationHeader()
            ->getJson(route('users.show', $user->id));

        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email
            ]);

        $user->forceDelete();
    }

    /**
     * Test that a user can update a specific user.
     *
     * @return void
     */
    public function test_user_can_update_specific_user()
    {
        $user = User::factory()->create();

        $updateData = [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
        ];

        $response = $this->withAuthorizationHeader()
            ->putJson(route('users.update', $user->id), $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'first_name' => 'Jane',
                'last_name' => 'Doe',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
        ]);

        $user->forceDelete();
    }

    /**
     * Test that a user cannot update a specific user with invalid inputs.
     *
     * @return void
     */
    public function test_user_cannot_update_specific_user_with_invalid_inputs()
    {
        $user = User::factory()->create();

        $updateData = [
            'first_name' => '',
            'last_name' => '',
        ];

        $response = $this->withAuthorizationHeader()
            ->putJson(route('users.update', $user->id), $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['first_name', 'last_name']);

        $user->forceDelete();
    }

    /**
     * Test that a user can delete a specific user.
     *
     * @return void
     */
    public function test_user_can_delete_specific_user()
    {
        $user = User::factory()->create();

        $response = $this->withAuthorizationHeader()
            ->deleteJson(route('users.destroy', $user->id));

        $response->assertStatus(204);

        $this->assertSoftDeleted($user);
    }
}
