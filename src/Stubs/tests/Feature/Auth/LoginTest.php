<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class LoginTest
 * @package Tests\Feature\Auth
 *
 * @group auth
 */
class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_display_login_form()
    {
        // Given

        // When
        $response = $this->get(route('login'));

        // Then
        $response->assertSuccessful();
    }

    /** @test */
    public function it_logs_user_in_with_correct_credentials()
    {
        // Given
        $user = factory(User::class)->create([
            'password' => bcrypt($password = 'random-password'),
        ]);

        // When
        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => $password,
        ]);

        // Then
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function it_will_not_login_user_with_wrong_password()
    {
        // Given
        $user = factory(User::class)->create([
            'password' => bcrypt($password = 'random-password'),
        ]);

        // When
        $response = $this->from(route('login'))
            ->post(route('login'), [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);

        // Then
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** @test */
    public function it_can_not_login_if_user_doesnt_exist()
    {
        // Given

        // When
        $response = $this->from(route('login'))
            ->post(route('login'), [
                'email' => 'doesnt-exist-email',
                'password' => 'wrong-password',
            ]);

        // Then
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** @test */
    public function it_allows_user_to_logout()
    {
        // Given
        $user = factory(User::class)->create();
        $this->be($user);

        // When
        $this->post(route('logout'));

        // Then
        $this->assertGuest();
    }
}