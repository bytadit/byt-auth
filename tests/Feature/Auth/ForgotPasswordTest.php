<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    protected function passwordRequestRoute()
    {
        return route('password.request');
    }
    protected function passwordEmailGetRoute()
    {
        return route('password.email');
    }
    protected function passwordEmailPostRoute()
    {
        return route('password.email');
    }

    public function test_user_can_view_forgot_password_form()
    // FOR-01
    {
        $response = $this->get($this->passwordRequestRoute());
        $response->assertSuccessful();
        $response->assertViewIs('auth.forgot-password');
    }
    public function test_user_receives_email_with_password_reset_link()
    // FOR-02
    {
        Notification::fake();
        $user = User::factory()->create([
            'email' => 'joni@example.com',
        ]);
        $response = $this->post($this->passwordEmailPostRoute(), [
            'email' => 'joni@example.com',
        ]);
        $this->assertNotNull($token = DB::table('password_resets')->first());
        Notification::assertSentTo($user, ResetPassword::class, function ($notification, $channels) use ($token) {
            return Hash::check($notification->token, $token->token) === true;
        });
    }
    public function test_user_does_not_receive_email_when_not_registered()
    // FOR-03
    {
        Notification::fake();
        $response = $this->from($this->passwordEmailGetRoute())->post($this->passwordEmailPostRoute(), [
            'email' => 'nobody@example.com',
        ]);
        $response->assertRedirect($this->passwordEmailGetRoute());
        $response->assertSessionHasErrors('email');
        Notification::assertNotSentTo(User::factory()->make(['email' => 'nobody@example.com']), ResetPassword::class);
    }
    public function test_email_is_required()
    // FOR-04
    {
        $response = $this->from($this->passwordEmailGetRoute())->post($this->passwordEmailPostRoute(), []);
        $response->assertRedirect($this->passwordEmailGetRoute());
        $response->assertSessionHasErrors('email');
    }
    public function test_email_format_is_not_valid()
    // FOR-05
    {
        $response = $this->from($this->passwordEmailGetRoute())->post($this->passwordEmailPostRoute(), [
            'email' => 'invalid-email',
        ]);
        $response->assertRedirect($this->passwordEmailGetRoute());
        $response->assertSessionHasErrors('email');
    }
}
