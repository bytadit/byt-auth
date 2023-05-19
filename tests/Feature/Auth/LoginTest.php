<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class LoginTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;
    protected function successfulLoginRoute()
    {
        return route('dashboard');
    }
    protected function loginGetRoute()
    {
        return route('login');
    }
    protected function loginPostRoute()
    {
        return route('login');
    }
    protected function logoutRoute()
    {
        return route('logout');
    }
    protected function successfulLogoutRoute()
    {
        return '/';
    }
    protected function guestMiddlewareRoute()
    {
        return route('dashboard');
    }
    protected function getTooManyLoginAttemptsMessage()
    {
        return sprintf('/^%s$/', str_replace('\:seconds', '\d+', preg_quote(__('auth.throttle'), '/')));
    }

    public function test_login_valid()
    // LOG-01
    {
        $user = User::factory()->create([
            'password' => bcrypt($password = 'Password_123'),
        ]);
        $response = $this->post($this->loginPostRoute(), [
            'email' => $user->email,
            'password' => $password,
        ]);
        $response->assertRedirect($this->successfulLoginRoute());
        $this->assertAuthenticatedAs($user);
    }
    public function test_login_incorrect_email()
    // LOG-02
    {
        $response = $this->from($this->loginGetRoute())->post($this->loginPostRoute(), [
            'email' => 'nobody@nothing.com',
            'password' => 'Password_123',
        ]);
        $response->assertRedirect($this->loginGetRoute());
        $response->assertSessionHasErrors('email');
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }
    public function test_login_incorrect_password()
    // LOG-03
    {
        $user = User::factory()->create([
            'password' => bcrypt($password = 'Password_123'),
        ]);
        $response = $this->from($this->loginGetRoute())->post($this->loginPostRoute(), [
            'email' => $user->email,
            'password' => 'Passwd_123',
        ]);
        $response->assertRedirect($this->loginGetRoute());
        $response->assertSessionHasErrors('email');
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }
    public function test_user_cannot_view_login_form_when_authenticated()
    // LOG-04
    {
        $user = User::factory()->make();
        $response = $this->actingAs($user)->get($this->loginGetRoute());
        $response->assertRedirect($this->guestMiddlewareRoute());
    }
    public function test_user_can_logout()
    // LOG-05
    {
        $this->be(User::factory()->create());
        $response = $this->post($this->logoutRoute());
        $response->assertRedirect($this->successfulLogoutRoute());
        $this->assertGuest();
    }
    public function test_user_cannot_logout_when_not_authenticated()
    // LOG-06
    {
        $response = $this->post($this->logoutRoute());
        $response->assertRedirect($this->successfulLogoutRoute());
        $this->assertGuest();
    }
    public function test_user_three_attempt_failed_login()
    // LOG-07
    {
        $user = User::factory()->create([
            'password' => Hash::make($password = 'Password_123'),
        ]);
        for($i=0; $i<3; $i++) {
            $response = $this->from($this->loginGetRoute())->post($this->loginPostRoute(), [
                'email' => $user->email,
                'password' => 'invalid-password',
            ]);
            $response->assertSessionHasErrors(['email']);
            $response->assertRedirect($this->loginGetRoute());
        }
        $response = $this->from($this->loginGetRoute())->post($this->loginPostRoute(), [
            'email' => $user->email,
            'password' => 'invalid-password',
        ]);
        $response->assertSessionHasNoErrors();
        sleep(12); //bcs the waiting throttle is 10s
        $response = $this->from($this->loginGetRoute())->post($this->loginPostRoute(), [
            'email' => $user->email,
            'password' => 'invalid-password',
        ]);
        $response->assertSessionHasErrors(['email']);
        $response->assertRedirect($this->loginGetRoute());
        $this->assertGuest();
    }



}
