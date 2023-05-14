<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Laravel\Fortify\Features;
use Laravel\Jetstream\Jetstream;

class RegisterTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    // use RefreshDatabase;

    public function test_valid_registration()
    {
        $name = rand(0, 1000);
        $response = $this->post('/register', [
            'name' => 'Aditya Bagus'. $name,
            'email' => 'adityabp'. $name . '@gmail.com',
            'password' => 'Password_123',
            'password_confirmation' => 'Password_123',
        ]);
        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
    }
    public function test_name_is_empty()
    {
        $name = rand(0, 1000);
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'adityabp'. $name . '@gmail.com',
            'password' => 'Password_123',
            'password_confirmation' => 'Password_123',
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasErrors('name');
    }
    public function test_email_is_empty()
    {
        $name = rand(0, 1000);
        $response = $this->post('/register', [
            'name' => 'Aditya Bagus' . $name,
            'email' => '',
            'password' => 'Password_123',
            'password_confirmation' => 'Password_123',
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
    }
    public function test_invalid_email_format()
    {
        $name = rand(0, 1000);
        $response = $this->post('/register', [
            'name' => 'Aditya Bagus' . $name,
            'email' => 'adityabp'. $name . '.gmail.com',
            'password' => 'Password_123',
            'password_confirmation' => 'Password_123',
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
    }
    public function test_email_has_been_used()
    {
        $name = rand(0, 1000);
        $user = User::all()->random()->first();
        $response = $this->post('/register', [
            'name' => 'Aditya Bagus' . $name,
            'email' => $user->email,
            'password' => 'Password_123',
            'password_confirmation' => 'Password_123',
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
    }
    public function test_password_confirm_not_match()
    {
        $name = rand(0, 1000);
        $response = $this->post('/register', [
            'name' => 'Aditya Bagus' . $name,
            'email' => 'adityabp'. $name . '@gmail.com',
            'password' => 'Password_1234',
            'password_confirmation' => 'Password_123',
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');
    }
    public function test_password_no_number()
    {
        $name = rand(0, 1000);
        $response = $this->post('/register', [
            'name' => 'Aditya Bagus' . $name,
            'email' => 'adityabp'. $name . '@gmail.com',
            'password' => 'Password_',
            'password_confirmation' => 'Password_',
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');
    }
    public function test_password_no_special_char()
    {
        $name = rand(0, 1000);
        $response = $this->post('/register', [
            'name' => 'Aditya Bagus' . $name,
            'email' => 'adityabp'. $name . '@gmail.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');
    }
    public function test_password_no_lowercase()
    {
        $name = rand(0, 1000);
        $response = $this->post('/register', [
            'name' => 'Aditya Bagus' . $name,
            'email' => 'adityabp'. $name . '@gmail.com',
            'password' => 'PASSWORD_123',
            'password_confirmation' => 'PASSWORD_123',
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');
    }
    public function test_password_no_uppercase()
    {
        $name = rand(0, 1000);
        $response = $this->post('/register', [
            'name' => 'Aditya Bagus' . $name,
            'email' => 'adityabp'. $name . '@gmail.com',
            'password' => 'password_123',
            'password_confirmation' => 'password_123',
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');
    }
    public function test_password_less_than_8_char()
    {
        $name = rand(0, 1000);
        $response = $this->post('/register', [
            'name' => 'Aditya Bagus' . $name,
            'email' => 'adityabp'. $name . '@gmail.com',
            'password' => 'Pass_12',
            'password_confirmation' => 'Pass_12',
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');
    }
    public function test_password_contain_spaces()
    {
        $name = rand(0, 1000);
        $response = $this->post('/register', [
            'name' => 'Aditya Bagus' . $name,
            'email' => 'adityabp'. $name . '@gmail.com',
            'password' => 'Password 123',
            'password_confirmation' => 'Password 123',
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');
    }
}
