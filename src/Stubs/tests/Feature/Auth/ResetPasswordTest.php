<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

/**
 * Class ResetPasswordTest
 * @package Tests\Feature\Auth
 *
 * @group auth
 */
class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param $user
     * @return mixed
     */
    private function getValidToken($user)
    {
        return Password::broker()->createToken($user);
    }
    
    /** @test */
    public function it_shows_password_reset_page()
    {
        // Given
        $user = factory(User::class)->create();
        $token = $this->getValidToken($user);
            
        // When
        $response = $this->get(route('password.reset', $token));
            
        // Then
        $response->assertSuccessful();
        $response->assertViewHas('token', $token);
    }
    
    /** @test */
    public function it_reset_password_with_valid_token()
    {
        // Given
        Event::fake();
        $user = factory(User::class)->create();
            
        // When
        $response = $this->post('/password/reset', [
            'token' => $this->getValidToken($user),
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);
            
        // Then
        $this->assertEquals($user->email, $user->fresh()->email);
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
        $this->assertAuthenticatedAs($user);
        Event::assertDispatched(PasswordReset::class, function ($e) use ($user) {
            return $e->user->id === $user->id;
        });
    }
    
    /** @test */
    public function it_doesnt_reset_password_with_invalid_token()
    {
        // Given
        Event::fake();
        $user = factory(User::class)->create([
            'password' => bcrypt('password')
        ]);
        $token = $this->getValidToken($user);
            
        // When
        $response = $this->from(route('password.reset', $token))->post('/password/reset', [
            'token' => str_random(24),
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);
            
        // Then
        $this->assertEquals($user->email, $user->fresh()->email);
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
        $this->assertGuest();
    }
    
    /** @test */
    public function it_doesnt_update_with_empty_password()
    {
        // Given
        Event::fake();
        $user = factory(User::class)->create([
            'password' => bcrypt('password')
        ]);
        $token = $this->getValidToken($user);

        // When
        $response = $this->from(route('password.reset', $token))->post('/password/reset', [
            'token' => str_random(24),
            'email' => $user->email,
            'password' => '',
            'password_confirmation' => '',
        ]);

        // Then
        $response->assertSessionHasErrors('password');
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
        $this->assertGuest();
    }
    
    /** @test */
    public function it_doesnt_update_password_with_blank_email()
    {
        // Given
        Event::fake();
        $user = factory(User::class)->create([
            'password' => bcrypt('password')
        ]);
        $token = $this->getValidToken($user);

        // When
        $response = $this->from(route('password.reset', $token))->post('/password/reset', [
            'token' => str_random(24),
            'email' => '',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        // Then
        $response->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
        $this->assertGuest();
            
    }
}