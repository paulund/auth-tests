<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Class ForgotPasswordTest
 * @package Tests\Feature\Auth
 *
 * @group auth
 */
class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_shows_password_form()
    {
        // Given

        // When
        $response = $this->get(
            route('password.request')
        );

        // Then
        $response->assertSuccessful();
        $response->assertViewIs('auth.passwords.email');
    }

    /** @test */
    public function it_will_send_an_email_to_user_with_reset_password_link()
    {
        // Given
        Notification::fake();
        $user = factory(User::class)->create();

        // When
        $response = $this->post(
            route('password.email'),
            [
                'email' => $user->email
            ]
        );

        // Then
        $this->assertNotNull($token = DB::table('password_resets')->first());
        Notification::assertSentTo($user, ResetPassword::class, function ($notification, $channels) use ($token) {
            return Hash::check($notification->token, $token->token) === true;
        });
    }

    /** @test */
    public function it_does_not_send_email_if_not_registered()
    {
        // Given
        Notification::fake();
        $user = factory(User::class)->make();

        // When
        $response = $this->from(route('password.email'))
            ->post(
                route('password.email'),
                [
                    'email' => $user->email
                ]
            );

        // Then
        $response->assertRedirect(route('password.email'));
        $response->assertSessionHasErrors('email');
        Notification::assertNotSentTo($user, ResetPassword::class);
    }

    /** @test */
    public function it_requires_email_on_post_form()
    {
        // Given

        // When
        $response = $this->from(route('password.email'))
            ->post(
                route('password.email'),
                []
            );

        // Then
        $response->assertRedirect(route('password.email'));
        $response->assertSessionHasErrors('email');
    }
}