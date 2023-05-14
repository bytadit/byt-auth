<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
    public function test_valid_login()
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
    public function test_login_invalid_email()
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
    public function test_login_invalid_password()
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
    public function testUserCannotViewALoginFormWhenAuthenticated()
    {
        $user = User::factory()->make();
        $response = $this->actingAs($user)->get($this->loginGetRoute());
        $response->assertRedirect($this->guestMiddlewareRoute());
    }

    public function test_user_can_logout()
    {
        $this->be(User::factory()->create());
        $response = $this->post($this->logoutRoute());
        $response->assertRedirect($this->successfulLogoutRoute());
        $this->assertGuest();
    }
    public function test_user_cannot_logout_when_not_authenticated()
    {
        $response = $this->post($this->logoutRoute());
        $response->assertRedirect($this->successfulLogoutRoute());
        $this->assertGuest();
    }

    public function testUserCannotMakeMoreThanFiveAttemptsInOneMinute()
    {
        $user = User::factory()->create([
            'password' => Hash::make($password = 'Password_123'),
        ]);
        for($i=0; $i<3; $i++) {
            $response = $this->from($this->loginGetRoute())->post($this->loginPostRoute(), [
                'email' => $user->email,
                'password' => 'invalid-password',
            ]);
            // $response->assertStatus();
            // $response->assertSessionHasErrors('password');
            $response->assertRedirect($this->loginGetRoute());
        }
        //then on the 6th you would expect to see the throtteler to stop the request
        $response = $this->from($this->loginGetRoute())->post($this->loginPostRoute(), [
            'email' => $user->email,
            'password' => 'invalid-password',
        ]);
        $response->assertRedirect($this->loginGetRoute());
        $this->assertGuest();

        // $user = User::factory()->create([
        //     'password' => Hash::make($password = 'i-love-laravel'),
        // ]);

        // for($i=0; $i<5; $i++) {
        //     $response = $this->from($this->loginGetRoute())->post($this->loginPostRoute(), [
        //         'email' => $user->email,
        //         'password' => 'invalid-password',
        //     ]);
        //     $response->assertRedirect($this->loginGetRoute());
        //     // $response->assertSessionHasErrors('email');
        // }
        // $response->assertSessionHasErrors('email');


        // $this->assertMatchesRegularExpression(
        //     $this->getTooManyLoginAttemptsMessage(),
        //     collect(
        //         $response
        //             ->baseResponse
        //             ->getSession()
        //             ->get('errors')
        //             ->getBag('default')
        //             ->get('email')
        //     )->first()
        // );
        // $this->assertTrue(session()->hasOldInput('email'));
        // $this->assertFalse(session()->hasOldInput('password'));
        // $this->assertGuest();

        // $user = User::factory()->create([
        //     'password' => bcrypt($password = 'Password_123'),
        // ]);

        // foreach (range(0, 5) as $_) {
        //     $response = $this->from($this->loginGetRoute())->post($this->loginPostRoute(), [
        //         'email' => $user->email,
        //         'password' => 'Passwd_123',
        //     ]);
        // }
        // $response->assertSessionHasErrors('email');
        // $response->assertRedirect($this->loginGetRoute());

        // $user = User::factory()->create([
        //     'password' => bcrypt($password = 'Password_123'),
        // ]);
        // foreach (range(0, 5) as $_) {
        //     $response = $this->from($this->loginGetRoute())->post($this->loginPostRoute(), [
        //         'email' => $user->email,
        //         'password' => 'Passwd_123',
        //     ]);
        // }

        // $response->assertRedirect($this->loginGetRoute());
        // $response->assertSessionHasErrors('email');
        // $this->assertTrue(session()->hasOldInput('email'));
        // $this->assertFalse(session()->hasOldInput('password'));
        // $this->assertGuest();

        // $this->assertMatchesRegularExpression(
        //     $this->getTooManyLoginAttemptsMessage(),
        //     collect(
        //         $response
        //             ->baseResponse
        //             ->getSession()
        //             ->get('errors')
        //             ->getBag('default')
        //             ->get('email')
        //     )->first()
        // );
        // $this->assertTrue(session()->hasOldInput('email'));
        // $this->assertFalse(session()->hasOldInput('password'));
        // $this->assertGuest();
    }



}
