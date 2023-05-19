<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class VerifyEmailTest extends TestCase
{
    use RefreshDatabase;
    protected $verificationVerifyRouteName = 'verification.verify';
    protected function successfulVerificationRoute()
    {
        return '/dashboard?verified=1';
    }
    protected function toDashboardVerificationRoute()
    {
        return route('dashboard');
    }
    protected function verificationNoticeRoute()
    {
        return route('verification.notice');
    }
    protected function validVerificationVerifyRoute($user)
    {
        return URL::signedRoute($this->verificationVerifyRouteName, [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ]);
    }
    protected function invalidVerificationVerifyRoute($user)
    {
        return route($this->verificationVerifyRouteName, [
            'id' => $user->id,
            'hash' => 'invalid-hash',
        ]);
    }
    protected function verificationResendRoute()
    {
        return route('verification.send');
    }
    protected function loginRoute()
    {
        return route('login');
    }

    public function test_guest_cannot_see_verification_notice()
    // VER-01
    {
        $response = $this->get($this->verificationNoticeRoute());
        $response->assertRedirect($this->loginRoute());
    }
    public function test_user_sees_verification_notice_when_not_verified()
    // VER-02
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);
        $response = $this->actingAs($user)->get($this->verificationNoticeRoute());
        $response->assertStatus(200);
        $response->assertViewIs('auth.verify-email');
    }
    public function test_verified_user_is_redirected_dashboard_when_visiting_verif_notice_route()
    // VER-03
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $response = $this->actingAs($user)->get($this->verificationNoticeRoute());
        $response->assertRedirect($this->toDashboardVerificationRoute());
    }
    public function test_guest_cannot_see_verification_verify_route()
    // VER-04
    {
        $user = User::factory()->create([
            'id' => 1,
            'email_verified_at' => null,
        ]);
        $response = $this->get($this->validVerificationVerifyRoute($user));
        $response->assertRedirect($this->loginRoute());
    }
    public function test_user_cannot_verify_others()
    // VER-05
    {
        $user = User::factory()->create([
            'id' => 1,
            'email_verified_at' => null,
        ]);
        $user2 = User::factory()->create(['id' => 2, 'email_verified_at' => null]);
        $response = $this->actingAs($user)->get($this->validVerificationVerifyRoute($user2));
        $response->assertForbidden();
        $this->assertFalse($user2->fresh()->hasVerifiedEmail());
    }
    public function test_user_is_redirected_to_correct_route_when_is_already_verified()
    // VER-06
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $response = $this->actingAs($user)->get($this->validVerificationVerifyRoute($user));
        $response->assertRedirect($this->successfulVerificationRoute());
    }
    public function test_forbidden_is_returned_when_signature_is_invalid_in_verification_verify_route()
    // VER-07
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $response = $this->actingAs($user)->get($this->invalidVerificationVerifyRoute($user));
        $response->assertStatus(403);
    }
    public function test_user_can_verify_themselves()
    // VER-08
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);
        $response = $this->actingAs($user)->get($this->validVerificationVerifyRoute($user));
        $response->assertRedirect($this->successfulVerificationRoute());
        $this->assertNotNull($user->fresh()->email_verified_at);
    }
    public function test_guest_cannot_resend_a_verification_email()
    // VER-09
    {
        $response = $this->post($this->verificationResendRoute());
        $response->assertRedirect($this->loginRoute());
    }
    public function test_user_is_redirected_to_correct_route_if_already_verified()
    // VER-10
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $response = $this->actingAs($user)->post($this->verificationResendRoute());
        $response->assertRedirect($this->toDashboardVerificationRoute());
    }

    public function test_user_can_resend_a_verification_email()
    // VER-11
    {
        Notification::fake();
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);
        $response = $this->actingAs($user)
            ->from($this->verificationNoticeRoute())
            ->post($this->verificationResendRoute());
        Notification::assertSentTo($user, VerifyEmail::class);
        $response->assertRedirect($this->verificationNoticeRoute());
    }
}
