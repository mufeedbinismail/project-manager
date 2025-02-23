<?php

namespace Tests\Traits;

use App\Models\User;

trait AuthenticatesUser
{
    /** 
     * Authentication Token
     * 
     * @var string $token
     * */
    protected $token;

    /** 
     * Authenticated User
     * 
     * @var \App\Models\User $user
     * */
    protected $user;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->initializeAuthentication();
    }

    /**
     * Tear down the test environment.
     */
    protected function tearDown(): void
    {
        $this->cleanupAuthentication();
        parent::tearDown();
    }

    /**
     * Initialize authentication for the test environment.
     */
    protected function initializeAuthentication(): void
    {
        // Create a user and get the access token
        $this->user = User::factory()->create();

        $response = $this->postJson(route('login'), [
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $this->token = $response->json('token');
    }

    /**
     * Clean up authentication for the test environment.
     */
    protected function cleanupAuthentication(): void
    {
        // Delete the access token using the user's relation
        $this->user->tokens()->delete();
        $this->user->forceDelete();
    }


    /**
     * Attach the Authorization header with the Bearer token to the request.
     *
     * @return $this
     */
    protected function withAuthorizationHeader()
    {
        return $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ]);
    }
}