<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('passport:install');
    }

    /** @test */
    public function a_user_can_register()
    {
        $response = $this->postJson('/api/v1/auth/register', [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'username' => 'johndoe',
                'email' => 'john.doe@mail.com',
                'phone_number' => '0000000000',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'first_name',
                    'last_name',
                    'username',
                    'created_at',
                ], 
                'access_token',
            ])
            ->assertJson([
                'user' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'username' => 'johndoe',
                ]
            ]);
        
        $this->assertCount(1, User::all());
    }

    /** @test */
    public function a_user_can_login()
    {
        factory(User::class)->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'johndoe',
            'email' => 'john.doe@mail.com',
            'phone_number' => '0000000000',
            'password' => 'password',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
                'email' => 'john.doe@mail.com',
                'password' => 'password',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'first_name',
                    'last_name',
                    'username',
                    'email',
                    'phone_number',
                    'created_at',
                ], 
                'access_token',
            ])
            ->assertJson([
                'user' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'username' => 'johndoe',
                    'email' => 'john.doe@mail.com',
                    'phone_number' => '0000000000',
                ]
            ]);
    }
}
