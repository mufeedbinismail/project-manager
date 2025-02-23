<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * Test that a user cannot access a protected route without a token.
     *
     * @return void
     */
    public function test_user_cannot_access_protected_route_without_token()
    {
        $response = $this->post(route('logout'));

        $response->assertStatus(401);
    }

    /**
     * Test that a user can login, logout, and cannot access a protected route with a revoked token.
     *
     * @return void
     */
    public function test_user_can_login_logout_and_cannot_access_protected_route_with_revoked_token()
    {
        $user = User::factory()->create();

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'token'
        ]);

        $token = $response->json('token');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post(route('logout'));

        $response->assertStatus(200);

        Auth::forgetGuards();
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post(route('logout'));

        $response->assertStatus(401);

        $user->tokens()->delete();
        $user->forceDelete();
    }

    /**
     * Test that a user cannot login with invalid credentials.
     *
     * @return void
     */
    public function test_user_cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->make();
        
        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password'
        ]);

        $response->assertStatus(401);
        
        $user->save();

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'pasword'
        ]);

        $response->assertStatus(401);

        $user->forceDelete();
    }

    /**
     * Test that a user can be registered with valid inputs.
     *
     * @return void
     */
    public function test_user_can_be_registered_with_valid_inputs()
    {
        $user = User::factory()->make()->toArray();
        $user['password'] = $user['password_confirmation'] = 'password';
        $response = $this->post(route('register'), $user);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'user' => [
                'id',
                'first_name',
                'last_name',
                'email',
                'created_at',
                'updated_at'
            ],
            'token'
        ]);

        $user = User::find($response->json('user.id'));
        $user->tokens()->delete();
        $user->forceDelete();
    }

    /**
     * Test that a user gets validation errors when registering with invalid inputs.
     *
     * @return void
     */
    public function test_user_gets_validation_errors_when_registering_with_invalid_inputs()
    {
        $response = $this->post(route('register'), [
            'first_name' => '',
            'last_name' => '',
            'email' => 'invalid-email',
            'password' => 'pass',
            'password_confirmation' => 'password'
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure([
            'errors' => [
                'first_name',
                'last_name',
                'email',
                'password'
            ]
        ]);
    }

    /**
     * Test that a user cannot register with a duplicate email.
     *
     * @return void
     */
    public function test_user_cannot_register_with_duplicate_email()
    {
        $user = User::factory()->create();

        // First registration
        $response = $this->post(route('register'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure([
            'errors' => [
                'email'
            ]
        ]);
    }
}
