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
    // REG-01
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
    public function test_register_name_is_empty()
    // REG-02
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
    public function test_register_email_is_empty()
    // REG-03
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
    public function test_register_invalid_email_format()
    // REG-04
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
    public function test_register_email_has_been_used()
    // REG-05
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
    public function test_register_password_confirm_not_match()
    // REG-06
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
    public function test_register_password_has_no_number()
    // REG-07
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
    public function test_register_password_has_no_special_char()
    // REG-08
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
    public function test_register_password_has_no_lowercase()
    // REG-09
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
    public function test_register_password_has_no_uppercase()
    // REG-10
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
    public function test_register_password_has_less_than_8_char()
    // REG-11
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
    public function test_register_password_contain_spaces()
    // REG-12
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
